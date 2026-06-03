<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Acceso Administrativo';
?>

<style>
.admin-login-wrap {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
}
.admin-login-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 32px rgba(0,0,0,.09);
    padding: 2.5rem 2.25rem;
    width: 100%;
    max-width: 400px;
}
.admin-logo {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, #1e40af, #1d4ed8);
    border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 1.25rem;
}
</style>

<div class="admin-login-wrap">
    <div class="admin-login-card">
        <div class="admin-logo">🔐</div>
        <h5 class="text-center fw-bold mb-1">Panel Administrativo</h5>
        <p class="text-center text-muted small mb-4">Instituto Tecnológico Superior de Valladolid</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput([
                'autofocus'   => true,
                'placeholder' => 'Usuario administrador',
                'class'       => 'form-control',
            ])->label('Usuario') ?>

            <?= $form->field($model, 'password')->passwordInput([
                'placeholder' => 'Contraseña',
                'class'       => 'form-control',
            ])->label('Contraseña') ?>

            <?= $form->field($model, 'rememberMe')->checkbox(['class' => 'form-check-input'])->label('Mantener sesión') ?>

            <div class="d-grid mt-3">
                <?= Html::submitButton('Acceder', [
                    'class' => 'btn btn-dark fw-semibold',
                    'name'  => 'login-button',
                ]) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
