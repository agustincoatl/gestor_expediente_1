<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var frontend\models\SignupForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Registro';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">

            <h1 class="mb-1"><?= Html::encode($this->title) ?></h1>
            <p class="text-muted mb-4">Crea tu cuenta para acceder al sistema de expedientes.</p>

<!--             <div class="alert alert-info py-2 mb-4" role="alert">
                <i class="ti ti-info-circle me-1"></i>
                Si tu correo es <strong>@valladolid.tecnm.mx</strong>, serás registrado automáticamente como <strong>Docente</strong>.
                Cualquier otro correo requiere seleccionar el rol de <strong>Consultor</strong>.
            </div> -->

            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                <?= $form->field($model, 'username')->textInput([
                    'autofocus'   => true,
                    'placeholder' => 'Nombre de usuario',
                ]) ?>

                <?= $form->field($model, 'email')->textInput([
                    'placeholder' => 'tu.nombre@valladolid.tecnm.mx',
                    'id'          => 'signup-email',
                ]) ?>

                <?= $form->field($model, 'password')->passwordInput([
                    'placeholder' => 'Contraseña (mínimo 6 caracteres)',
                ]) ?>

                <!-- Selector de rol: solo visible si el correo NO es institucional -->
                <div id="rol-field" style="display:none;">
                    <?= $form->field($model, 'role_id')->dropDownList(
                        [3 => 'Consultor'],
                        ['prompt' => 'Selecciona tu rol']
                    )->hint('Los docentes se asignan automáticamente por dominio de correo.') ?>
                </div>

                <div class="d-grid mt-3">
                    <?= Html::submitButton('Crear cuenta', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>

            <p class="text-center text-muted mt-3 small">
                ¿Ya tienes cuenta? <?= Html::a('Inicia sesión', ['/site/login']) ?>
            </p>

        </div>
    </div>
</div>

<script>
(function () {
    const emailInput = document.getElementById('signup-email');
    const rolField   = document.getElementById('rol-field');
    const DOMINIO    = '@valladolid.tecnm.mx';

    function checkEmail() {
        const esDocente = emailInput.value.toLowerCase().trim().endsWith(DOMINIO);
        rolField.style.display = esDocente ? 'none' : 'block';
    }

    emailInput.addEventListener('input', checkEmail);
    checkEmail();
})();
</script>
    