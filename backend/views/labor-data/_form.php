<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var common\models\LaborData $model */
/** @var common\models\Academy[] $allAcademies */
/** @var int|null $academyId ID de la academia seleccionada */
?>

<div class="labor-data-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'entry_date')->input('date', [
        'max' => date('Y-m-d'),
    ])->label('Fecha de ingreso') ?>

    <div class="mb-3">
        <?= Html::label('Academia', 'academy_id', ['class' => 'form-label fw-bold']) ?>
        <?= Html::dropDownList(
            'academy_id',
            $academyId ?? null,
            ArrayHelper::map($allAcademies, 'id', 'academy_name'),
            [
                'id'     => 'academy_id',
                'class'  => 'form-select',
                'prompt' => 'Selecciona una academia',
            ]
        ) ?>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
