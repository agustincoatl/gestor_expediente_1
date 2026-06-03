<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Teaching;

/** @var yii\web\View $this */
/** @var common\models\EmergencyContact $model */
/** @var bool $isAdmin */
?>

<div class="emergency-contact-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput([
        'maxlength'   => true,
        'placeholder' => 'Nombre completo del contacto',
    ])->label('Nombre') ?>

   <?= $form->field($model, 'number_phone')->textInput([
    'maxlength'   => 10,
    'placeholder' => 'Teléfono de contacto',
    'type'        => 'text',
    'inputmode'   => 'numeric', 
    'pattern'     => '[0-9]*',
    'oninput'     => 'this.value = this.value.replace(/[^0-9]/g, "").slice(0,10);'
    ])->label('Teléfono') ?>

    <?= $form->field($model, 'parentesco')->dropDownList([
        'Padre' => 'Padre',
        'Madre' => 'Madre', 
        'Esposa(o)' => 'Esposo(a)',
        'Hermano(a)' => 'Hermano(a)',
        'Otro' => 'Otro',
        //'maxlength'   => true,
    ],['prompt' => 'Selecciona el tipo de parentesco', 'class' => 'form-select']) ?>

    <?php if ($isAdmin): ?>
        <?= $form->field($model, 'teaching_id')->dropDownList(
            ArrayHelper::map(Teaching::find()->all(), 'id', function($t) {
                return $t->name . ' ' . $t->first_last_name;
            }),
            ['prompt' => 'Selecciona el docente']
        )->label('Docente') ?>
    <?php else: ?>
        <?= $form->field($model, 'teaching_id')->hiddenInput()->label(false) ?>
    <?php endif; ?>

    <?php if (isset($recordId) && $recordId): ?>
        <?= Html::hiddenInput('record_id', $recordId) ?>
    <?php endif; ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
        <?php if (isset($recordId) && $recordId): ?>
            <?= Html::a('Cancelar', ['/record/view', 'id' => $recordId], ['class' => 'btn btn-secondary ms-2']) ?>
        <?php endif; ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>