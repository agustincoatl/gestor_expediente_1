<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\ChangePasswordForm $model */

$this->title = 'Cambiar contraseña';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-change-password" style="max-width: 520px; margin: 0 auto;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h4 mb-3"><?= Html::encode($this->title) ?></h1>

            <?php $form = ActiveForm::begin(['id' => 'change-password-form']); ?>

            <?= $form->field($model, 'currentPassword')->passwordInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'newPassword')->passwordInput() ?>
            <?= $form->field($model, 'repeatPassword')->passwordInput() ?>

            <div class="d-flex gap-2 mt-3">
                <?= Html::submitButton('Guardar contraseña', ['class' => 'btn btn-success']) ?>
                <?= Html::a('Cancelar', ['/site/index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
