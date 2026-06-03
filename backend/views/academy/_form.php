<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Academy $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="academy-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'academy_name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
