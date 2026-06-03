<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\User;
use common\models\Record;
use common\models\EstatusExpediente;

/** @var yii\web\View $this */
/** @var common\models\Teaching $model */
/** @var bool $expedienteActivo */
/** @var array $camposEditables */

$isAdmin          = !Yii::$app->user->isGuest && Yii::$app->user->identity->role_id == 1;
$camposEditables  = $camposEditables ?? ['gender', 'email', 'phone_number', 'rfc'];

// Detectar estado del expediente directamente desde la BD
$record   = (!$model->isNewRecord && $model->id)
    ? Record::findOne(['teaching_id' => $model->id])
    : null;
$recordId = $record->id ?? null;

// expedienteActivo = docente con expediente en estado Activo (estatus_id = 2)
$expedienteActivo = !$isAdmin
    && !$model->isNewRecord
    && $record !== null
    && (int)$record->estatus_id === EstatusExpediente::ACTIVO;

// Helpers
$editable = fn(string $campo): bool =>
    $isAdmin || !$expedienteActivo || in_array($campo, $camposEditables);

// Renderiza un campo de solo lectura con el valor actual
$readonlyField = function(string $label, $value) {
    return '<div class="mb-3">
        <label class="form-label text-muted small fw-semibold">' . Html::encode($label) . '</label>
        <div class="form-control bg-light text-muted" style="cursor:default; user-select:none;">'
            . Html::encode($value ?: '—')
        . '</div>
    </div>';
};
?>

<div class="teaching-form">

<?php if ($expedienteActivo && !$isAdmin): ?>
<div class="alert alert-info d-flex gap-2 align-items-start mb-4" role="alert">
    <span style="font-size:1.2rem;">🔒</span>
    <div>
        <strong>Expediente activo.</strong>
        Puedes actualizar tu género, correo, teléfono y RFC.
        Para cualquier otro cambio contacta al administrador.
    </div>
</div>
<?php endif; ?>

<?php $form = ActiveForm::begin(); ?>

<?php /* ── Nombre completo ── */
if ($editable('name')): ?>
    <?= $form->field($model, 'name')->textInput([
        'maxlength' => true, 'placeholder' => 'Nombre(s)', 'class' => 'form-control',
    ]) ?>
<?php else: ?>
    <?= $readonlyField('Nombre(s)', $model->name) ?>
    <?= Html::activeHiddenInput($model, 'name') ?>
<?php endif; ?>

<?php if ($editable('first_last_name')): ?>
    <?= $form->field($model, 'first_last_name')->textInput([
        'maxlength' => true, 'placeholder' => 'Apellido Paterno', 'class' => 'form-control',
    ]) ?>
<?php else: ?>
    <?= $readonlyField('Apellido Paterno', $model->first_last_name) ?>
    <?= Html::activeHiddenInput($model, 'first_last_name') ?>
<?php endif; ?>

<?php if ($editable('second_last_name')): ?>
    <?= $form->field($model, 'second_last_name')->textInput([
        'maxlength' => true, 'placeholder' => 'Apellido Materno', 'class' => 'form-control',
    ]) ?>
<?php else: ?>
    <?= $readonlyField('Apellido Materno', $model->second_last_name) ?>
    <?= Html::activeHiddenInput($model, 'second_last_name') ?>
<?php endif; ?>

<?php if ($editable('born_date')): ?>
    <?= $form->field($model, 'born_date')->input('date', ['class' => 'form-control']) ?>
<?php else: ?>
    <?= $readonlyField('Fecha de Nacimiento', $model->born_date) ?>
    <?= Html::activeHiddenInput($model, 'born_date') ?>
<?php endif; ?>

<?php if ($editable('curp')): ?>
    <?= $form->field($model, 'curp')->textInput([
        'maxlength'   => 18,
        'placeholder' => 'CURP (18 caracteres)',
        'class'       => 'form-control',
        'style'       => 'text-transform:uppercase;',
        'oninput'     => 'this.value=this.value.replace(/\s/g,"").toUpperCase()',
    ]) ?>
<?php else: ?>
    <?= $readonlyField('CURP', $model->curp) ?>
    <?= Html::activeHiddenInput($model, 'curp') ?>
<?php endif; ?>

<?php /* ── Campos siempre editables para el docente ── */ ?>

<?= $form->field($model, 'gender')->dropDownList([
    'Masculino' => 'Masculino',
    'Femenino'  => 'Femenino',
    'Otro'      => 'Otro',
], ['prompt' => 'Selecciona género', 'class' => 'form-select']) ?>

<?= $form->field($model, 'email')->textInput([
    'maxlength' => true, 'placeholder' => 'Correo electrónico', 'class' => 'form-control',
]) ?>

<?= $form->field($model, 'phone_number')->textInput([
    'maxlength'   => 10,
    'placeholder' => 'Teléfono',
    'type'        => 'text',
    'inputmode'   => 'numeric', // teclado numérico en móviles
    'pattern'     => '[0-9]*',
    'oninput'     => 'this.value = this.value.replace(/[^0-9]/g, "").slice(0,10);'
    ])->label('Teléfono') ?>

<?= $form->field($model, 'rfc')->textInput([
    'maxlength'   => 13,
    'placeholder' => 'RFC',
    'class'       => 'form-control',
    'style'       => 'text-transform:uppercase;',
    'oninput'     => 'this.value=this.value.replace(/\s/g,"").toUpperCase()',
]) ?>

<?php if ($isAdmin): ?>
    <?= $form->field($model, 'user_id')->dropDownList(
        ArrayHelper::map(User::find()->all(), 'id', 'username'),
        ['prompt' => 'Selecciona usuario', 'class' => 'form-select']
    ) ?>
<?php else: ?>
    <?= $form->field($model, 'user_id')->hiddenInput(['value' => Yii::$app->user->id])->label(false) ?>
<?php endif; ?>

<div class="d-flex gap-2 mt-3">
    <?= Html::submitButton('💾 Guardar cambios', ['class' => 'btn btn-success']) ?>
    <?php if ($recordId): ?>
        <?= Html::a('← Volver al expediente', ['/record/view', 'id' => $recordId],
            ['class' => 'btn btn-outline-secondary']) ?>
    <?php endif; ?>
</div>

<?php ActiveForm::end(); ?>

</div>