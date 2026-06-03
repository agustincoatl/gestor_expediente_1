<?php

namespace frontend\controllers;

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
                'class'   => VerbFilter::className(),
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
        $query       = Document::find();

        if (!$isAdmin && !$isConsultor) {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                Yii::$app->session->setFlash('warning', 'Primero debes registrar tus datos personales.');
                return $this->redirect(['/teaching/create']);
            }
            if (!$teaching->isProfileComplete()) {
                Yii::$app->session->setFlash('warning', 'Completa tus datos personales antes de continuar.');
                return $this->redirect(['/teaching/update', 'id' => $teaching->id]);
            }
            $record = Record::findOne(['teaching_id' => $teaching->id]);
            if (!$record) {
                Yii::$app->session->setFlash('warning', 'Primero debes completar tus datos laborales.');
                return $this->redirect(['/labor-data/create']);
            }
            $query->andWhere(['record_id' => $record->id]);
        }

        $dataProvider = $searchModel->searchWithQuery($this->request->queryParams, $query);

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
        return $this->render('view', ['model' => $model]);
    }

    public function actionCreate()
    {
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;
        $model   = new Document();
        $recordId = Yii::$app->request->get('record_id');

        $record = null;
        if ($isAdmin) {
            if ($recordId) {
                $record = Record::findOne($recordId);
            }
        } else {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                Yii::$app->session->setFlash('warning', 'Primero debes registrar tus datos personales.');
                return $this->redirect(['/teaching/create']);
            }
            if (!$teaching->isProfileComplete()) {
                Yii::$app->session->setFlash('warning', 'Completa tus datos personales antes de continuar.');
                return $this->redirect(['/teaching/update', 'id' => $teaching->id]);
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
            if (!$isAdmin && $record) {
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
                $recordId = $recordId ?? ($record ? $record->id : null);
                Yii::$app->session->setFlash('success', 'Documento subido.');
                // Redirigir de nuevo al create para subir más documentos, conservando record_id
                if ($recordId) {
                    return $this->redirect(['/record/view', 'id' => $recordId]);
                }
                return $this->redirect(['index']);
            }
        }

        $documentTypes = ArrayHelper::map(DocumentType::find()->all(), 'id', 'type_name');
        $records       = $isAdmin ? ArrayHelper::map(Record::find()->all(), 'id', 'id') : [];

        $recordId = $recordId ?? ($record ? $record->id : null);
        $hasDocuments = false;
        if ($record) {
            $hasDocuments = Document::find()->where(['record_id' => $record->id])->exists();
        }

        return $this->render('create', [
            'model'         => $model,
            'documentTypes' => $documentTypes,
            'records'       => $records,
            'record'        => $record,
            'isAdmin'       => $isAdmin,
            'recordId'      => $recordId,
            'hasDocuments'  => $hasDocuments,
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
                return $this->redirect(['index']);
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
            throw new NotFoundHttpException('El archivo no existe en el servidor.');
        }

        return Yii::$app->response->sendFile($filePath, $model->document_name);
    }

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

        // Documentos bloqueados solo si el expediente está Inactivo
        if ($record->isInactivo()) {
            throw new ForbiddenHttpException('Tu expediente está inactivo. Contacta al administrador.');
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
