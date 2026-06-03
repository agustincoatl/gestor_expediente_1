<?php

namespace frontend\controllers;

use common\models\Teaching;
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
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        // Admin no puede crear expedientes
        if ($isAdmin) {
            Yii::$app->session->setFlash('warning', 'El administrador no puede crear expedientes. El docente debe registrar el suyo.');
            return $this->redirect(['index']);
        }

        // Docente solo puede tener un expediente
        $existente = Teaching::findOne(['user_id' => $user->id]);
        if ($existente) {
            if (!$existente->isProfileComplete()) {
                Yii::$app->session->setFlash('warning', 'Completa tus datos personales antes de continuar.');
                return $this->redirect(['update', 'id' => $existente->id]);
            }
            Yii::$app->session->setFlash('warning', 'Ya tienes un expediente registrado.');
            return $this->redirect(['view', 'id' => $existente->id]);
        }

        $model = new Teaching();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->user_id = $user->id;
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Datos personales guardados.');
                    return $this->redirect(['/record/index']);
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model'   => $model,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function actionUpdate($id)
    {
        $model   = $this->findModel($id);
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        $this->checkAccess($model);

        // Determinar si el expediente está Activo (docente con campos restringidos)
        $record          = \common\models\Record::findOne(['teaching_id' => $model->id]);
        // estatus_id: 1=Registro, 2=Activo, 3=Inactivo
        $expedienteActivo = !$isAdmin && $record && (int)$record->estatus_id === 2;

        if ($this->request->isPost) {
            if ($expedienteActivo) {
                // Solo cargar los campos permitidos
                $post = Yii::$app->request->post('Teaching', []);
                foreach (self::$camposEditables as $campo) {
                    if (isset($post[$campo])) {
                        $model->$campo = $post[$campo];
                    }
                }
            } else {
                $model->load($this->request->post());
            }

            if (!$isAdmin) {
                $model->user_id = $user->id;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Datos actualizados correctamente.');
                $recordId = Yii::$app->request->get('record_id') ?? ($record ? $record->id : null);
                if ($recordId) {
                    return $this->redirect(['/record/view', 'id' => $recordId]);
                }
                return $this->redirect(['/record/index']);
                //return $this->redirect(['view', 'id' => $model->id]);
            }
        }

         return $this->render('update', [
            'model'            => $model,
            'isAdmin'          => $isAdmin,
            'expedienteActivo' => $expedienteActivo,
            'camposEditables'  => self::$camposEditables,
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
    /**
     * Campos que el docente puede editar aunque el expediente esté Activo.
     */
    public static $camposEditables = ['gender', 'email', 'phone_number', 'rfc'];

    protected function checkAccess(Teaching $model, bool $soloLectura = false)
    {
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if (!$isAdmin && $model->user_id !== $user->id) {
            throw new ForbiddenHttpException('No tienes permiso para ver este expediente.');
        }

        // En modo edición, docente con expediente Activo solo puede tocar los campos permitidos
        // (el bloqueo real de campos se hace en actionUpdate con safe attributes)
        if (!$isAdmin && !$soloLectura) {
            $record = \common\models\Record::findOne(['teaching_id' => $model->id]);
            if ($record && (int)$record->estatus_id === 3) {
                Yii::$app->session->setFlash('warning', 'Tu expediente está inactivo. Contacta al administrador.');
                throw new ForbiddenHttpException('El expediente está inactivo.');
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
