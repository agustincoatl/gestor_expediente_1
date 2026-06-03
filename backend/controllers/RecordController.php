<?php

namespace backend\controllers;

use common\models\Record;
use common\models\Document;
use common\models\EstatusExpediente;
use common\models\Teaching;
use common\models\search\RecordSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use Yii;

class RecordController extends Controller
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
        $searchModel = new RecordSearch();

        $query = Record::find();

        // Consultor no ve expedientes Inactivos
        if ($isConsultor) {
            $query->andWhere(['!=', 'estatus_id', EstatusExpediente::INACTIVO]);
        }

        // Docente → redirigir directo a su expediente
        if (!$isAdmin && !$isConsultor) {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                Yii::$app->session->setFlash('warning', 'Primero debes registrar tus datos personales.');
                return $this->redirect(['/teaching/create']);
            }
            $record = Record::findOne(['teaching_id' => $teaching->id]);
            if (!$record) {
                Yii::$app->session->setFlash('info', 'Tu expediente se crea automáticamente al registrar tus datos laborales.');
                return $this->redirect(['/labor-data/create']);
            }
            return $this->redirect(['view', 'id' => $record->id]);
        }

        // Consultor no ve Inactivos
        if ($isConsultor) {
            $query->andWhere(['!=', 'estatus_id', EstatusExpediente::INACTIVO]);
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

    public function actionDownloadDocuments($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);

        try {
            $zip = Document::createZipForRecord($model);
        } catch (\RuntimeException $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if (!$zip) {
            Yii::$app->session->setFlash('warning', 'No hay documentos disponibles para descargar.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        Yii::$app->response->on(Response::EVENT_AFTER_SEND, static function () use ($zip) {
            if (is_file($zip['path'])) {
                unlink($zip['path']);
            }
        });

        return Yii::$app->response->sendFile($zip['path'], $zip['name']);
    }

    /**
     * El record NO se crea manualmente — se crea automático desde LaborDataController.
     * Solo admin puede crearlo manualmente si es necesario.
     */
    public function actionCreate()
    {
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if (!$isAdmin) {
            Yii::$app->session->setFlash('info', 'Tu expediente se crea automáticamente al registrar tus datos laborales.');
            return $this->redirect(['/labor-data/create']);
        }

        $model = new Record();

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->creation_date = date('Y-m-d H:i:s');
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model   = $this->findModel($id);
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if (!$isAdmin) {
            throw new ForbiddenHttpException('Solo el administrador puede modificar el expediente directamente.');
        }

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if (!$isAdmin) {
            throw new ForbiddenHttpException('No tienes permiso para eliminar expedientes.');
        }

        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function checkAccess(Record $model)
    {
        $user        = Yii::$app->user->identity;
        $isAdmin     = $user->role_id == 1;
        $isConsultor = $user->role_id == 3;

        // Consultor no puede ver expedientes Inactivos
        if ($isConsultor && $model->isInactivo()) {
            throw new ForbiddenHttpException('Este expediente no está disponible.');
        }

        if ($isAdmin || $isConsultor) return;

        // Docente solo ve su propio expediente
        $teaching = Teaching::findOne(['user_id' => $user->id]);
        if (!$teaching || $model->teaching_id !== $teaching->id) {
            throw new ForbiddenHttpException('No tienes permiso para ver este expediente.');
        }

        // Docente con expediente Inactivo no puede acceder
        if ($model->isInactivo()) {
            Yii::$app->session->setFlash('danger', 'Tu expediente ha sido desactivado. Contacta al administrador.');
            throw new ForbiddenHttpException('Tu expediente está inactivo.');
        }
    }

    public function actionUpdateStatus($id, $status)
    {
        $model   = $this->findModel($id);
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        $mapa = [
            'Registro' => EstatusExpediente::REGISTRO,
            'Activo'   => EstatusExpediente::ACTIVO,
            'Inactivo' => EstatusExpediente::INACTIVO,
        ];

        if (!array_key_exists($status, $mapa)) {
            throw new \yii\web\BadRequestHttpException('Estado no válido.');
        }

        // Docente solo puede finalizar su propio expediente: Registro → Activo
        if (!$isAdmin) {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching || $model->teaching_id !== $teaching->id) {
                throw new ForbiddenHttpException('No tienes permiso sobre este expediente.');
            }
            if ($status !== 'Activo' || !$model->isRegistro()) {
                throw new ForbiddenHttpException('Solo puedes finalizar tu expediente cuando está en Registro.');
            }
        }

        $model->estatus_id = $mapa[$status];
        $model->save(false);

        $mensajes = [
            'Activo'   => '✅ Tu expediente ha sido finalizado y está activo.',
            'Inactivo' => '🚫 El expediente ha sido marcado como Inactivo.',
            'Registro' => 'El expediente fue regresado a Registro para edición.',
        ];
        Yii::$app->session->setFlash('success', $mensajes[$status] ?? 'Estado actualizado.');
        return $this->redirect(['view', 'id' => $model->id]);
    }

    protected function findModel($id)
    {
        if (($model = Record::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El expediente no existe.');
    }
}
