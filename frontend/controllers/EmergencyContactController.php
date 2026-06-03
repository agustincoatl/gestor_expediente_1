<?php

namespace frontend\controllers;

use common\models\EmergencyContact;
use common\models\Teaching;
use common\models\Record;
use common\models\search\EmergencyContactSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use Yii;

class EmergencyContactController extends Controller
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
        $searchModel = new EmergencyContactSearch();
        $query       = EmergencyContact::find();

        if (!$isAdmin) {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                Yii::$app->session->setFlash('warning', 'Primero debes registrar tus datos personales.');
                return $this->redirect(['/teaching/create']);
            }
            if (!$teaching->isProfileComplete()) {
                Yii::$app->session->setFlash('warning', 'Completa tus datos personales antes de continuar.');
                return $this->redirect(['/teaching/update', 'id' => $teaching->id]);
            }
            $query->andWhere(['teaching_id' => $teaching->id]);
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
        $model   = new EmergencyContact();

        $teachingId = null;
        if (!$isAdmin) {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                Yii::$app->session->setFlash('warning', 'Primero debes registrar tus datos personales.');
                return $this->redirect(['/teaching/create']);
            }
            if (!$teaching->isProfileComplete()) {
                Yii::$app->session->setFlash('warning', 'Completa tus datos personales antes de continuar.');
                return $this->redirect(['/teaching/update', 'id' => $teaching->id]);
            }
            $count = EmergencyContact::find()->where(['teaching_id' => $teaching->id])->count();
            $record = Record::findOne(['teaching_id' => $teaching->id]);
            if ($count >= 2) {
                Yii::$app->session->setFlash('warning', 'Ya tienes el máximo de 2 contactos de emergencia registrados.');
                return $record ? $this->redirect(['/record/view', 'id' => $record->id]) : $this->redirect(['/record/index']);
            }
            $teachingId = $teaching->id;
            $model->teaching_id = $teachingId; // ✅ AQUÍ — asignar antes del isPost
        }

        $recordId = null;
        if (isset($_GET['record_id'])) {
            $recordId = (int)$_GET['record_id'];
        }

        if ($this->request->isPost) {
            $model->load($this->request->post());
            if (!$isAdmin) {
                $model->teaching_id = $teachingId; // doble seguridad, lo reafirmas
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Contacto de emergencia registrado.');
                $recordId = $recordId ?: ($record->id ?? null);
                if ($recordId) {
                    return $this->redirect(['/record/view', 'id' => $recordId]);
                }
                $record = Record::findOne(['teaching_id' => $model->teaching_id]);
                return $record ? $this->redirect(['/record/view', 'id' => $record->id]) : $this->redirect(['/record/index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model'    => $model,
            'isAdmin'  => $isAdmin,
            'recordId' => $recordId,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);

        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if ($this->request->isPost && $model->load($this->request->post())) {
            if (!$isAdmin) {
                // docente no puede cambiar el teaching_id
                $teaching = Teaching::findOne(['user_id' => $user->id]);
                $model->teaching_id = $teaching->id;
            }
            if ($model->save()) {
                return $this->redirect(['/record/index']);
            }
        }

        return $this->render('update', [
            'model'   => $model,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);
        $record = Record::findOne(['teaching_id' => $model->teaching_id]);
        $model->delete();
        return $record ? $this->redirect(['/record/view', 'id' => $record->id]) : $this->redirect(['/record/index']);
    }

    protected function checkAccess(EmergencyContact $model)
    {
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;
        if ($isAdmin) return;

        $teaching = Teaching::findOne(['user_id' => $user->id]);
        if (!$teaching || $model->teaching_id !== $teaching->id) {
            throw new ForbiddenHttpException('No tienes permiso para acceder a este contacto.');
        }
        // Contactos de emergencia son editables siempre (incluso con expediente Activo)
        // Solo bloqueamos si el expediente está Inactivo
        $record = Record::findOne(['teaching_id' => $teaching->id]);
        if ($record && (int)$record->estatus_id === 3) {
            throw new ForbiddenHttpException('Tu expediente está inactivo. Contacta al administrador.');
        }
    }

    protected function findModel($id)
    {
        if (($model = EmergencyContact::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El registro no existe.');
    }
}
