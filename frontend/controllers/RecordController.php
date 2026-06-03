<?php

namespace frontend\controllers;

use common\models\EstatusExpediente;
use common\models\Document;
use common\models\LaborAcademy;
use common\models\Record;
use common\models\Teaching;
use common\models\search\RecordSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;
        $isConsultor = $user->role_id == 3;
        $searchModel = new RecordSearch();

        $query = Record::find();

        if ($isConsultor) {
            $query->andWhere(['!=', 'estatus_id', EstatusExpediente::INACTIVO]);
        }

        if (!$isAdmin && !$isConsultor) {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                $teaching = new Teaching();
                $teaching->user_id = $user->id;
            }

            $record = $teaching->isNewRecord ? null : Record::findOne(['teaching_id' => $teaching->id]);
            if ($record) {
                return $this->redirect(['view', 'id' => $record->id]);
            }

            $record = new Record();
            $record->teaching_id = $teaching->id ?: null;

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => null,
                'isAdmin' => false,
                'isDocente' => true,
                'teaching' => $teaching,
                'record' => $record,
                'checklist' => $this->buildChecklist($teaching, $record),
            ]);
        }

        $dataProvider = $searchModel->searchWithQuery($this->request->queryParams, $query);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'isAdmin' => $isAdmin,
            'isDocente' => false,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->checkAccess($model);

        return $this->render('view', [
            'model' => $model,
            'checklist' => $this->buildChecklist($model->teaching, $model),
        ]);
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

    public function actionCreate()
    {
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if (!$isAdmin) {
            Yii::$app->session->setFlash('info', 'Tu expediente se crea automaticamente al registrar tus datos laborales.');
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
        $model = $this->findModel($id);
        $user = Yii::$app->user->identity;
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
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        if (!$isAdmin) {
            throw new ForbiddenHttpException('No tienes permiso para eliminar expedientes.');
        }

        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function checkAccess(Record $model)
    {
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;
        $isConsultor = $user->role_id == 3;

        if ($isConsultor && $model->isInactivo()) {
            throw new ForbiddenHttpException('Este expediente no esta disponible.');
        }

        if ($isAdmin || $isConsultor) {
            return;
        }

        $teaching = Teaching::findOne(['user_id' => $user->id]);
        if (!$teaching || $model->teaching_id !== $teaching->id) {
            throw new ForbiddenHttpException('No tienes permiso para ver este expediente.');
        }

        if ($model->isInactivo()) {
            Yii::$app->session->setFlash('danger', 'Tu expediente ha sido desactivado. Contacta al administrador.');
            throw new ForbiddenHttpException('Tu expediente esta inactivo.');
        }
    }

    public function actionUpdateStatus($id, $status)
    {
        $model = $this->findModel($id);
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role_id == 1;

        $mapaEstatus = [
            'Registro' => EstatusExpediente::REGISTRO,
            'Activo' => EstatusExpediente::ACTIVO,
            'Inactivo' => EstatusExpediente::INACTIVO,
        ];

        if (!array_key_exists($status, $mapaEstatus)) {
            throw new \yii\web\BadRequestHttpException('Status no valido.');
        }

        if ($status === 'Activo') {
            $checklist = $this->buildChecklist($model->teaching, $model);
            if (!$checklist['isComplete']) {
                Yii::$app->session->setFlash('warning', 'Completa los datos pendientes antes de finalizar tu expediente.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        if (!$isAdmin) {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching || $model->teaching_id !== $teaching->id) {
                throw new ForbiddenHttpException('No tienes permiso sobre este expediente.');
            }
            if ($status !== 'Activo' || (int)$model->estatus_id !== EstatusExpediente::REGISTRO) {
                throw new ForbiddenHttpException('Solo puedes finalizar tu expediente cuando esta en Registro.');
            }
        }

        $model->estatus_id = $mapaEstatus[$status];
        $model->save(false);

        $mensajes = [
            'Activo' => 'Tu expediente ha sido finalizado y esta activo.',
            'Inactivo' => 'El expediente ha sido marcado como Inactivo.',
            'Registro' => 'El expediente fue regresado a Registro.',
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

    private function buildChecklist(?Teaching $teaching, ?Record $record): array
    {
        $teachingExists = $teaching && !$teaching->isNewRecord;
        $recordExists = $record && !$record->isNewRecord;
        $laborData = $recordExists ? $record->laborData : null;
        $documents = $recordExists ? $record->documents : [];
        $contacts = $teachingExists ? $teaching->emergencyContacts : [];
        $laborAcademyCount = $laborData ? LaborAcademy::find()->where(['labor_id' => $laborData->id])->count() : 0;

        $recordId = $recordExists ? $record->id : null;
        $teachingUrl = $teachingExists
            ? ['/teaching/update', 'id' => $teaching->id, 'record_id' => $recordId]
            : ['/teaching/create'];
        $laborUrl = $laborData
            ? ['/labor-data/update', 'id' => $laborData->id, 'record_id' => $recordId]
            : ['/labor-data/create'];
        $contactUrl = $recordExists ? ['/emergency-contact/create', 'record_id' => $record->id] : ['/emergency-contact/create'];
        $documentUrl = $recordExists ? ['/document/create', 'record_id' => $record->id] : ['/document/create'];

        $personalFields = [
            ['label' => 'Nombre(s)', 'done' => $teachingExists && trim((string)$teaching->name) !== '', 'value' => $teachingExists ? $teaching->name : null, 'url' => $teachingUrl],
            ['label' => 'Apellido paterno', 'done' => $teachingExists && trim((string)$teaching->first_last_name) !== '', 'value' => $teachingExists ? $teaching->first_last_name : null, 'url' => $teachingUrl],
            ['label' => 'Apellido materno', 'done' => $teachingExists && trim((string)$teaching->second_last_name) !== '', 'value' => $teachingExists ? $teaching->second_last_name : null, 'url' => $teachingUrl],
            ['label' => 'Fecha de nacimiento', 'done' => $teachingExists && trim((string)$teaching->born_date) !== '', 'value' => $teachingExists ? $teaching->born_date : null, 'url' => $teachingUrl],
            ['label' => 'CURP', 'done' => $teachingExists && trim((string)$teaching->curp) !== '', 'value' => $teachingExists ? $teaching->curp : null, 'url' => $teachingUrl],
            ['label' => 'Genero', 'done' => $teachingExists && trim((string)$teaching->gender) !== '', 'value' => $teachingExists ? $teaching->gender : null, 'url' => $teachingUrl],
            ['label' => 'Correo electronico', 'done' => $teachingExists && trim((string)$teaching->email) !== '', 'value' => $teachingExists ? $teaching->email : null, 'url' => $teachingUrl],
            ['label' => 'Telefono', 'done' => $teachingExists && trim((string)$teaching->phone_number) !== '', 'value' => $teachingExists ? $teaching->phone_number : null, 'url' => $teachingUrl],
            ['label' => 'RFC', 'done' => $teachingExists && trim((string)$teaching->rfc) !== '', 'value' => $teachingExists ? $teaching->rfc : null, 'url' => $teachingUrl],
        ];

        $sections = [
            [
                'title' => 'Datos personales',
                'actionLabel' => $teachingExists ? 'Actualizar datos' : 'Capturar datos',
                'url' => $teachingUrl,
                'items' => $personalFields,
            ],
            [
                'title' => 'Datos laborales',
                'actionLabel' => $laborData ? 'Actualizar datos laborales' : 'Capturar datos laborales',
                'url' => $laborUrl,
                'items' => [
                    ['label' => 'Fecha de ingreso', 'done' => $laborData && trim((string)$laborData->entry_date) !== '', 'value' => $laborData ? $laborData->entry_date : null, 'url' => $laborUrl],
                    ['label' => 'Academia', 'done' => (int)$laborAcademyCount > 0, 'value' => (int)$laborAcademyCount > 0 ? $laborAcademyCount . ' registrada' : null, 'url' => $laborUrl],
                ],
            ],
            [
                'title' => 'Contactos de emergencia',
                'actionLabel' => 'Agregar contacto',
                'url' => $contactUrl,
                'items' => [
                    ['label' => 'Al menos un contacto', 'done' => count($contacts) > 0, 'value' => count($contacts) > 0 ? count($contacts) . ' registrado(s)' : null, 'url' => $contactUrl],
                ],
            ],
            [
                'title' => 'Documentos',
                'actionLabel' => 'Subir documento',
                'url' => $documentUrl,
                'items' => [
                    ['label' => 'Al menos un documento', 'done' => count($documents) > 0, 'value' => count($documents) > 0 ? count($documents) . ' subido(s)' : null, 'url' => $documentUrl],
                ],
            ],
        ];

        $total = 0;
        $done = 0;
        $nextUrl = null;

        foreach ($sections as $section) {
            foreach ($section['items'] as $item) {
                $total++;
                if ($item['done']) {
                    $done++;
                } elseif ($nextUrl === null) {
                    $nextUrl = $item['url'];
                }
            }
        }

        return [
            'sections' => $sections,
            'total' => $total,
            'done' => $done,
            'pending' => $total - $done,
            'percent' => $total > 0 ? (int)round(($done / $total) * 100) : 0,
            'isComplete' => $total > 0 && $done === $total,
            'nextUrl' => $nextUrl,
        ];
    }
}
