<?php

namespace frontend\controllers;

use common\models\LaborData;
use common\models\LaborAcademy;
use common\models\Record;
use common\models\Teaching;
use common\models\Academy;
use common\models\search\LaborDataSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use Yii;

class LaborDataController extends Controller
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
        $searchModel = new LaborDataSearch();

        $query = LaborData::find();

        if (!$isAdmin) {
            // Docente: filtrar solo su labor_data via su record
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
            if (!$record || !$record->labor_data_id) {
                // No tiene datos laborales aún, redirigir a crearlos
                Yii::$app->session->setFlash('info', 'Aún no tienes datos laborales registrados.');
                return $this->redirect(['create']);
            }
            // ← AQUÍ estaba el problema: faltaba este filtro
            $query->andWhere(['id' => $record->labor_data_id]);
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
        return $this->render('view', [
            'model'     => $model,
            'academies' => $model->academies,
        ]);
    }

    public function actionCreate()
    {
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

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
            $record = Record::findOne(['teaching_id' => $teaching->id]);
            if ($record && $record->labor_data_id) {
                Yii::$app->session->setFlash('warning', 'Ya tienes datos laborales registrados.');
                return $this->redirect(['/record/view', 'id' => $record->id]);
            }
        }

        $model     = new LaborData();
        $academyId = null;

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $academyId = (int) Yii::$app->request->post('academy_id', 0);

            if ($model->save()) {
                if ($academyId) {
                    LaborAcademy::deleteAll(['labor_id' => $model->id]);
                    $la             = new LaborAcademy();
                    $la->labor_id   = $model->id;
                    $la->academy_id = $academyId;
                    $la->save();
                }

                if (!$isAdmin) {
                    $teaching = Teaching::findOne(['user_id' => $user->id]);
                    $record   = Record::findOne(['teaching_id' => $teaching->id]);
                    if (!$record) {
                        $record                = new Record();
                        $record->teaching_id   = $teaching->id;
                        $record->estatus_id    = 1; // 1=Registro
                        $record->creation_date = date('Y-m-d H:i:s');
                    }
                    $record->labor_data_id = $model->id;
                    $record->save();

                    Yii::$app->session->setFlash('success', 'Datos laborales guardados.');
                    return $this->redirect(['/record/view', 'id' => $record->id]);
                }

                Yii::$app->session->setFlash('success', 'Datos laborales guardados.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        $allAcademies = Academy::find()->orderBy('id')->all();

        return $this->render('create', [
            'model'        => $model,
            'allAcademies' => $allAcademies,
            'academyId'    => $academyId,
            'isAdmin'      => $isAdmin,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);

        $firstLa   = $model->laborAcademies ? $model->laborAcademies[0] : null;
        $academyId = $firstLa ? $firstLa->academy_id : null;

        if ($this->request->isPost) {
            $model->load($this->request->post());
            $newAcademyId = (int) Yii::$app->request->post('academy_id', 0);

            if ($model->save()) {
                LaborAcademy::deleteAll(['labor_id' => $model->id]);
                if ($newAcademyId) {
                    $la             = new LaborAcademy();
                    $la->labor_id   = $model->id;
                    $la->academy_id = $newAcademyId;
                    $la->save();
                }
                $recordId = Yii::$app->request->get('record_id');
                if (!$recordId) {
                    $record = Record::findOne(['labor_data_id' => $model->id]);
                    $recordId = $record->id ?? null;
                }
                if ($recordId) {
                    return $this->redirect(['/record/view', 'id' => $recordId]);
                }
                return $this->redirect(['view', 'id' => $model->id]);
            }
            $academyId = $newAcademyId;
        }

        $allAcademies = Academy::find()->orderBy('id')->all();

        return $this->render('update', [
            'model'        => $model,
            'allAcademies' => $allAcademies,
            'academyId'    => $academyId,
        ]);
    }

    public function actionDelete($id)
    {
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if (!$isAdmin) {
            throw new ForbiddenHttpException('No tienes permiso para eliminar datos laborales.');
        }

        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Verifica que el docente solo acceda a sus propios datos laborales
     */
    protected function checkAccess(LaborData $model)
    {
        $user    = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if ($isAdmin) return;

        $teaching = Teaching::findOne(['user_id' => $user->id]);
        $record   = $teaching ? Record::findOne(['teaching_id' => $teaching->id]) : null;

        if (!$record || $record->labor_data_id !== $model->id) {
            throw new ForbiddenHttpException('No tienes permiso para acceder a estos datos.');
        }

        // Bloquear edición si el expediente ya fue finalizado
        if ((int)$record->estatus_id !== 1) {
            Yii::$app->session->setFlash('warning', 'Tu expediente ya fue finalizado. Para realizar cambios, contacta al administrador.');
            throw new ForbiddenHttpException('El expediente no está en estado editable.');
        }
    }

    protected function findModel($id)
    {
        if (($model = LaborData::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('El registro no existe.');
    }
}
