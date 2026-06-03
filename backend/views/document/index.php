<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\search\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $isAdmin */

$this->title = 'Documentos';
$this->params['breadcrumbs'][] = $this->title;

$documentsByTeacher = [];
foreach ($dataProvider->getModels() as $document) {
    $record = $document->record;
    $teaching = $record ? $record->teaching : null;
    $key = $teaching ? 'teacher-' . $teaching->id : 'without-teacher';

    if (!isset($documentsByTeacher[$key])) {
        $documentsByTeacher[$key] = [
            'teaching' => $teaching,
            'record' => $record,
            'documents' => [],
        ];
    }

    $documentsByTeacher[$key]['documents'][] = $document;
}
?>
<div class="document-index">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Crear documento', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => ['index'],
                'options' => ['class' => 'row g-3 align-items-end'],
            ]); ?>

            <div class="col-12 col-md-5">
                <?= $form->field($searchModel, 'teacher_name')->textInput([
                    'placeholder' => 'Nombre o correo del docente',
                ])->label('Docente') ?>
            </div>
            <div class="col-12 col-md-5">
                <?= $form->field($searchModel, 'document_name')->textInput([
                    'placeholder' => 'Nombre del archivo',
                ])->label('Documento') ?>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary w-100']) ?>
                <?= Html::a('Limpiar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?php if (empty($documentsByTeacher)): ?>
        <div class="alert alert-info">No se encontraron documentos.</div>
    <?php endif; ?>

    <?php foreach ($documentsByTeacher as $group): ?>
        <?php
        $teaching = $group['teaching'];
        $record = $group['record'];
        $teacherName = $teaching ? $teaching->nombreCompleto : 'Sin docente asignado';
        $teacherEmail = $teaching ? $teaching->email : null;
        ?>
        <div class="card mb-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h5 mb-1"><?= Html::encode($teacherName) ?></h2>
                    <div class="text-muted small">
                        <?php if ($teacherEmail): ?>
                            <?= Html::encode($teacherEmail) ?>
                        <?php endif; ?>
                        <?php if ($record): ?>
                            <span class="ms-2">Expediente #<?= Html::encode($record->id) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($record): ?>
                    <?= Html::a('Ver expediente', ['/record/view', 'id' => $record->id], [
                        'class' => 'btn btn-sm btn-outline-primary',
                    ]) ?>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Archivo</th>
                            <th>Fecha de subida</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($group['documents'] as $document): ?>
                            <tr>
                                <td><?= Html::encode($document->documentType->type_name ?? '-') ?></td>
                                <td><?= Html::encode($document->document_name ?? '-') ?></td>
                                <td><?= Html::encode($document->upload_date ?? '-') ?></td>
                                <td class="text-end">
                                    <?= Html::a('Ver', ['view', 'id' => $document->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?= Html::a('Editar', ['update', 'id' => $document->id, 'record_id' => $document->record_id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                    <?= Html::a('Descargar', ['download', 'id' => $document->id], ['class' => 'btn btn-sm btn-outline-success']) ?>
                                    <?= Html::a('Eliminar', ['delete', 'id' => $document->id], [
                                        'class' => 'btn btn-sm btn-outline-danger',
                                        'data' => [
                                            'confirm' => 'Seguro que deseas eliminar este documento?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>
