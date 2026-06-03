<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Record $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="record-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'teaching_id')->textInput() ?>

    <?= $form->field($model, 'estatus_id')->dropDownList(
        \yii\helpers\ArrayHelper::map(
            \common\models\EstatusExpediente::find()->all(), 'id', 'descripcion'
        ),
        ['prompt' => 'Selecciona estado']
    ) ?>

    <?= $form->field($model, 'creation_date')->textInput() ?>

    <?= $form->field($model, 'labor_data_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
