<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Iniciar sesión';
?>

<style>
.auth-wrap {
    min-height: calc(100vh - 120px);
    display: flex;
    align-items: center;
    justify-content: center;
}
.auth-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 32px rgba(0,0,0,.08);
    padding: 2.5rem 2.25rem;
    width: 100%;
    max-width: 420px;
}
.auth-logo {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 1.25rem;
}
</style>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">📋</div>
        <h4 class="text-center fw-bold mb-1">Bienvenido</h4>
        <p class="text-center text-muted small mb-4">Ingresa tus credenciales para continuar</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput([
                'autofocus'   => true,
                'placeholder' => 'Usuario',
                'class'       => 'form-control',
            ])->label('Usuario') ?>

            <?= $form->field($model, 'password')->passwordInput([
                'placeholder' => 'Contraseña',
                'class'       => 'form-control',
            ])->label('Contraseña') ?>

            <?= $form->field($model, 'rememberMe')->checkbox(['class' => 'form-check-input'])->label('Mantener sesión iniciada') ?>

            <div class="d-grid mt-3">
                <?= Html::submitButton('Iniciar sesión', [
                    'class' => 'btn btn-primary fw-semibold',
                    'name'  => 'login-button',
                ]) ?>
            </div>

        <?php ActiveForm::end(); ?>

        <hr class="my-3">

        <p class="text-center text-muted small mb-0">
            ¿No tienes cuenta? <?= Html::a('Regístrate aquí', ['/site/signup'], ['class' => 'fw-semibold']) ?>
        </p>
        <p class="text-center small mt-2">
            <?= Html::a('¿Olvidaste tu contraseña?', ['site/request-password-reset'], ['class' => 'text-muted']) ?>
        </p>
    </div>
</div>
