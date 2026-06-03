<?php

use common\models\Record;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Document $model */
/** @var array $documentTypes */
/** @var common\models\Record|null $record */
/** @var bool $isAdmin */
/** @var int|null $recordId */

$recordId = $recordId ?? ($record ? $record->id : null);
?>

<div class="document-form">

    <?php if ($record && $record->teaching): ?>
        <div class="alert alert-info py-2 mb-3">
            <i class="ti ti-folder me-1"></i>
            Subiendo documento para el expediente de
            <strong><?= Html::encode($record->teaching->nombreCompleto ?? 'Docente') ?></strong>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'document_type_id')->dropDownList(
        $documentTypes,
        ['prompt' => 'Selecciona el tipo de documento', 'class' => 'form-select']
    )->label('Tipo de Documento') ?>

    <?php if ($isAdmin && !$record): ?>
        <?php
        $allRecords = ArrayHelper::map(
            Record::find()->with('teaching')->all(),
            'id',
            function ($record) {
                $nombre = $record->teaching->nombreCompleto ?? 'Sin datos';
                return "Exp. #{$record->id} - {$nombre}";
            }
        );
        ?>
        <?= $form->field($model, 'record_id')->dropDownList(
            $allRecords,
            ['prompt' => 'Selecciona el expediente', 'class' => 'form-select']
        )->label('Expediente') ?>
    <?php elseif ($recordId): ?>
        <?= Html::activeHiddenInput($model, 'record_id', ['value' => $recordId]) ?>
    <?php endif; ?>

    <?= $form->field($model, 'archivo')->fileInput([
        'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
        'class'  => 'form-control',
    ])->label('Archivo (PDF, Word, imagen - max. 10MB)') ?>

    <?php if (!$model->isNewRecord && $model->document_path): ?>
        <div class="mb-3">
            <label class="form-label">Archivo actual:</label>
            <div>
                <?= Html::a(
                    Html::encode($model->document_name),
                    ['/document/download', 'id' => $model->id],
                    ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']
                ) ?>
                <small class="text-muted ms-2">Sube un nuevo archivo solo si deseas reemplazarlo.</small>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <?= Html::submitButton('Guardar documento', ['class' => 'btn btn-success']) ?>
        <?php if ($recordId): ?>
            <?= Html::a('Volver al expediente', ['/record/view', 'id' => $recordId], ['class' => 'btn btn-outline-secondary']) ?>
        <?php endif; ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
