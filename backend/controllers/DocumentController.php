<?php

namespace backend\controllers;

use common\models\Document;
use common\models\DocumentType;
use common\models\Record;
use common\models\Teaching;
use common\models\search\DocumentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use Yii;

class DocumentController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => ['delete' => ['POST']],
            ],
        ]);
    }

    public function actionIndex()
    {
        $user        = Yii::$app->user->identity;
        $isAdmin     = $user->role_id == 1;
        $isConsultor = $user->role_id == 3;
        $searchModel = new DocumentSearch();

        $query = Document::find();

        if (!$isAdmin && !$isConsultor) {
            // Docente: filtrar solo sus documentos via su record
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                Yii::$app->session->setFlash('warning', 'Primero debes registrar tus datos personales.');
                return $this->redirect(['/teaching/create']);
            }
            $record = Record::findOne(['teaching_id' => $teaching->id]);
            if (!$record) {
                Yii::$app->session->setFlash('warning', 'Primero debes completar tus datos laborales.');
                return $this->redirect(['/labor-data/create']);
            }
            // ← AQUÍ estaba el problema: faltaba este filtro
            $query->andWhere(['record_id' => $record->id]);
        }

        $dataProvider = $searchModel->searchWithQuery($this->request->queryParams, $query);
        if ($isAdmin) {
            $dataProvider->pagination = false;
        }

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'isAdmin'      => $isAdmin,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate()
    {
        $user     = Yii::$app->user->identity;
        $isAdmin  = $user->role_id == 1;
        $model    = new Document();
        $recordId = Yii::$app->request->get('record_id');

        $record = null;
        if ($isAdmin) {
            // Admin: usar el record_id del GET directamente
            if ($recordId) {
                $record = Record::findOne($recordId);
            }
        } else {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                Yii::$app->session->setFlash('warning', 'Primero debes registrar tus datos personales.');
                return $this->redirect(['/teaching/create']);
            }
            $record = Record::findOne(['teaching_id' => $teaching->id]);
            if (!$record) {
                Yii::$app->session->setFlash('warning', 'Primero debes completar tus datos laborales.');
                return $this->redirect(['/labor-data/create']);
            }
        }

        if ($this->request->isPost) {
            $model->load($this->request->post());

            if (!$record && $model->record_id) {
                $record = Record::findOne($model->record_id);
            }
            if ($record) {
                $model->record_id = $record->id;
            }

            $file = UploadedFile::getInstance($model, 'archivo');
            if ($file) {
                $savedFile = Document::saveUploadedFileForRecord($file, $record);
                if ($savedFile) {
                    $model->document_name = $savedFile['name'];
                    $model->document_path = $savedFile['path'];
                }
            }

            $model->upload_date = date('Y-m-d H:i:s');

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Documento subido correctamente.');
                $backId = $recordId ?? ($record ? $record->id : null);
                if ($backId) {
                    return $this->redirect(['/record/view', 'id' => $backId]);
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $documentTypes = ArrayHelper::map(DocumentType::find()->all(), 'id', 'type_name');

        return $this->render('create', [
            'model'         => $model,
            'documentTypes' => $documentTypes,
            'record'        => $record,
            'isAdmin'       => $isAdmin,
            'recordId'      => $recordId,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);

        $user          = Yii::$app->user->identity;
        $isAdmin       = $user->role_id == 1;
        $documentTypes = ArrayHelper::map(DocumentType::find()->all(), 'id', 'type_name');

        if ($this->request->isPost) {
            $model->load($this->request->post());

            $record = $model->record ?: ($model->record_id ? Record::findOne($model->record_id) : null);
            $file = UploadedFile::getInstance($model, 'archivo');
            if ($file) {
                $savedFile = Document::saveUploadedFileForRecord($file, $record);
                if ($savedFile) {
                    $model->document_name = $savedFile['name'];
                    $model->document_path = $savedFile['path'];
                }
            }

            if ($model->save()) {
                $recordId = Yii::$app->request->get('record_id') ?? $model->record_id;
                if ($recordId) {
                    return $this->redirect(['/record/view', 'id' => $recordId]);
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model'         => $model,
            'documentTypes' => $documentTypes,
            'isAdmin'       => $isAdmin,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);

        if ($model->document_path) {
            $filePath = Yii::getAlias('@backend/web/') . $model->document_path;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $recordId = $model->record_id;
        $model->delete();

        if ($recordId) {
            return $this->redirect(['/record/view', 'id' => $recordId]);
        }
        return $this->redirect(['index']);
    }

    public function actionDownload($id)
    {
        $model    = $this->findModel($id);
        $this->checkAccess($model);
        $filePath = Yii::getAlias('@backend/web/') . $model->document_path;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('El archivo no existe.');
        }

        return Yii::$app->response->sendFile($filePath, $model->document_name);
    }

    /**
     * Verifica que el docente solo acceda a sus propios documentos
     */
    protected function checkAccess(Document $model)
    {
        $user        = Yii::$app->user->identity;
        $isAdmin     = $user->role_id == 1;
        $isConsultor = $user->role_id == 3;

        if ($isAdmin || $isConsultor) return;

        $teaching = Teaching::findOne(['user_id' => $user->id]);
        $record   = $teaching ? Record::findOne(['teaching_id' => $teaching->id]) : null;

        if (!$record || $model->record_id !== $record->id) {
            throw new ForbiddenHttpException('No tienes permiso para acceder a este documento.');
        }
    }

    protected function findModel($id)
    {
        if (($model = Document::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El registro no existe.');
    }
}
