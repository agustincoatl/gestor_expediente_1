<?php

namespace backend\controllers;

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

        $recordId   = (int) Yii::$app->request->get('record_id', 0) ?: null;
        $teachingId = null;

        if ($isAdmin) {
            // Admin: obtener el teaching_id desde el expediente
            if ($recordId) {
                $record = Record::findOne($recordId);
                if ($record) {
                    $teachingId = $record->teaching_id;
                    $model->teaching_id = $teachingId;
                    // Verificar límite de 2 contactos
                    $count = EmergencyContact::find()->where(['teaching_id' => $teachingId])->count();
                    if ($count >= 2) {
                        Yii::$app->session->setFlash('warning', 'Este docente ya tiene el máximo de 2 contactos registrados.');
                        return $this->redirect(['/record/view', 'id' => $recordId]);
                    }
                }
            }
        } else {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                Yii::$app->session->setFlash('warning', 'Primero debes registrar tus datos personales.');
                return $this->redirect(['/teaching/create']);
            }
            $count = EmergencyContact::find()->where(['teaching_id' => $teaching->id])->count();
            if ($count >= 2) {
                Yii::$app->session->setFlash('warning', 'Ya tienes el máximo de 2 contactos de emergencia registrados.');
                return $this->redirect(['/record/index']);
            }
            $teachingId         = $teaching->id;
            $model->teaching_id = $teachingId;
        }

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $model->teaching_id = $teachingId; // siempre forzar el correcto
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Contacto de emergencia registrado.');
                if ($recordId) {
                    return $this->redirect(['/record/view', 'id' => $recordId]);
                }
                return $this->redirect(['/record/index']);
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

        $user     = Yii::$app->user->identity;
        $isAdmin  = $user->role_id == 1;
        $recordId = Yii::$app->request->get('record_id');

        // Si no viene en GET, intentar obtenerlo desde el expediente del teaching
        if (!$recordId) {
            $record   = Record::findOne(['teaching_id' => $model->teaching_id]);
            $recordId = $record->id ?? null;
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            if (!$isAdmin) {
                $teaching = Teaching::findOne(['user_id' => $user->id]);
                $model->teaching_id = $teaching->id;
            }
            if ($model->save()) {
                if ($recordId) {
                    return $this->redirect(['/record/view', 'id' => $recordId]);
                }
                return $this->redirect(['/record/index']);
            }
        }

        return $this->render('update', [
            'model'    => $model,
            'isAdmin'  => $isAdmin,
            'recordId' => $recordId,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);

        // Guardar el record_id antes de borrar para redirigir
        $record   = Record::findOne(['teaching_id' => $model->teaching_id]);
        $recordId = $record->id ?? null;

        $model->delete();
        Yii::$app->session->setFlash('success', 'Contacto eliminado.');

        if ($recordId) {
            return $this->redirect(['/record/view', 'id' => $recordId]);
        }
        return $this->redirect(['/record/index']);
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
    }

    protected function findModel($id)
    {
        if (($model = EmergencyContact::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El registro no existe.');
    }
}