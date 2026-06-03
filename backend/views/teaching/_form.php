<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\User;

/** @var yii\web\View $this */
/** @var common\models\Teaching $model */
/** @var yii\widgets\ActiveForm $form */

$isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->role_id == 1;
?>

<div class="teaching-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Nombre(s)']) ?>

    <?= $form->field($model, 'first_last_name')->textInput(['maxlength' => true, 'placeholder' => 'Apellido Paterno']) ?>

    <?= $form->field($model, 'second_last_name')->textInput(['maxlength' => true, 'placeholder' => 'Apellido Materno']) ?>

    <?= $form->field($model, 'born_date')->input('date') ?>

    <?= $form->field($model, 'curp')->textInput([
        'maxlength' => 18,
        'style'     => 'text-transform:uppercase;',
        'oninput'   => 'this.value=this.value.replace(/\s/g,"").toUpperCase()',
        'placeholder' => 'CURP (18 caracteres)',
    ]) ?>

    <?= $form->field($model, 'gender')->dropDownList([
        'Masculino' => 'Masculino',
        'Femenino'  => 'Femenino',
        'Otro'      => 'Otro',
    ], ['prompt' => 'Selecciona género']) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'placeholder' => 'Correo electrónico']) ?>

    <?= $form->field($model, 'phone_number')->textInput([
        'maxlength'   => 10,
        'placeholder' => 'Teléfono',
        'type'        => 'text', 
        'inputmode'    => 'numeric',
        'pattern'     => '[0-9]*',
        'oninput'     => 'this.value = this.value.replace(/[^0-9]/g, "").slice(0,10);']) ?>

    <?= $form->field($model, 'rfc')->textInput([
        'maxlength' => 13,
        'style'     => 'text-transform:uppercase;',
        'oninput'   => 'this.value=this.value.replace(/\s/g,"").toUpperCase()',
        'placeholder' => 'RFC',
    ]) ?>

   <!--  <?php if ($isAdmin): ?>
        <?= $form->field($model, 'user_id')->dropDownList(
            ArrayHelper::map(User::find()->all(), 'id', 'username'),
            ['prompt' => 'Selecciona usuario']
        ) ?> -->
    <?php else: ?>
        <?= $form->field($model, 'user_id')->hiddenInput(['value' => Yii::$app->user->id])->label(false) ?>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
