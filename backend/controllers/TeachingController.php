<?php

namespace backend\controllers;

use common\models\Teaching;
use common\models\Record;
use common\models\search\TeachingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * TeachingController implements the CRUD actions for Teaching model.
 */
class TeachingController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Teaching models.
     * Admin ve todos, Docente solo el suyo.
     */
    public function actionIndex()
    {
        $user     = Yii::$app->user->identity;
        $isAdmin  = $user->role_id == 1;

        $searchModel  = new TeachingSearch();
        $query        = Teaching::find();

        if (!$isAdmin) {
            $query->andWhere(['user_id' => $user->id]);
        }

        $dataProvider = $searchModel->search($this->request->queryParams, $query);

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

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Solo el Docente puede crear, y solo si no tiene expediente previo.
     */
    public function actionCreate()
    {
        $user     = Yii::$app->user->identity;
        $isAdmin  = $user->role_id == 1;
        $recordId = Yii::$app->request->get('record_id');
        $model    = new Teaching();

        if ($isAdmin) {
            // Admin puede crear datos personales para cualquier docente desde un expediente
            if ($this->request->isPost && $model->load($this->request->post())) {
                if ($model->save()) {
                    if ($recordId) {
                        $record = Record::findOne($recordId);
                        if ($record && !$record->teaching_id) {
                            $record->teaching_id = $model->id;
                            $record->save(false);
                        }
                        return $this->redirect(['/record/view', 'id' => $recordId]);
                    }
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            } else {
                $model->loadDefaultValues();
            }
            return $this->render('create', [
                'model'    => $model,
                'isAdmin'  => true,
                'recordId' => $recordId,
            ]);
        }

        // Docente solo puede tener un registro
        $existente = Teaching::findOne(['user_id' => $user->id]);
        if ($existente) {
            Yii::$app->session->setFlash('warning', 'Ya tienes datos personales registrados.');
            return $this->redirect(['view', 'id' => $existente->id]);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->user_id = $user->id;
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Datos personales guardados correctamente.');
                    if ($recordId) {
                        return $this->redirect(['/record/view', 'id' => $recordId]);
                    }
                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model'    => $model,
            'isAdmin'  => false,
            'recordId' => $recordId,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);

        $isAdmin = Yii::$app->user->identity->role_id == 1;

        if ($this->request->isPost && $model->load($this->request->post())) {
            if (!$isAdmin) {
                $model->user_id = Yii::$app->user->id; // docente no puede cambiar su user_id
            }
            if ($model->save()) {
                $recordId = Yii::$app->request->get('record_id');
                if ($recordId) {
                    return $this->redirect(['/record/view', 'id' => $recordId]);
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model'   => $model,
            'isAdmin' => $isAdmin,
        ]);
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

    /**
     * Verifica que el usuario tenga acceso al modelo.
     */
    protected function checkAccess(Teaching $model)
    {
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if (!$isAdmin && $model->user_id !== $user->id) {
            throw new ForbiddenHttpException('No tienes permiso para ver este expediente.');
        }

        // Docente no puede editar si su expediente ya no está Incompleto
        if (!$isAdmin) {
            $record = \common\models\Record::findOne(['teaching_id' => $model->id]);
            if ($record && $record->status !== 'Incompleto') {
                Yii::$app->session->setFlash('warning', 'Tu expediente ya fue finalizado. Para realizar cambios, contacta al administrador.');
                throw new ForbiddenHttpException('El expediente no está en estado editable.');
            }
        }
    }

    protected function findModel($id)
    {
        if (($model = Teaching::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El registro no existe.');
    }
}
