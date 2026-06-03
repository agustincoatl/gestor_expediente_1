<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var backend\models\Record $model */

$teaching          = $model->teaching;
$laborData         = $model->laborData;
$documents         = $model->documents;
$emergencyContacts = $teaching ? $teaching->emergencyContacts : [];

$this->title = 'Expediente #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Expedientes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$user        = Yii::$app->user->identity;
$isAdmin     = $user->role_id == 1;
$isConsultor = $user->role_id == 3;

// Colores por status
$statusColors = [
    'Registro' => 'warning',
    'Activo'     => 'success',
    'Inactivo'   => 'danger',
];
$statusColor = $statusColors[$model->estatusDescripcion] ?? 'secondary';

// ID del expediente para pasar a sub-controladores
$recordId = $model->id;
?>

<div class="record-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- BARRA DE ACCIONES -->
    <div class="mb-4 d-flex align-items-center gap-2 flex-wrap">

        <span class="badge bg-<?= $statusColor ?> fs-6">
            <?= Html::encode($model->estatusDescripcion ?? 'Registro') ?>
        </span>
        <small class="text-muted">Creado: <?= $model->creation_date ?></small>

        <?php if (!$isAdmin && !$isConsultor && $model->isActivo()): ?>
            <span class="ms-2 text-success small"><i>✔ Expediente finalizado. Para realizar cambios, contacta al administrador.</i></span>
        <?php elseif (!$isAdmin && !$isConsultor && $model->isInactivo()): ?>
            <span class="ms-2 text-danger small"><i>⚠ Tu expediente está inactivo. Contacta al administrador.</i></span>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <!-- Admin cambia el status -->
            <div class="ms-auto d-flex gap-2">
                <?php if (!$model->isActivo()): ?>
                    <?= Html::a('✅ Marcar Activo', ['update-status', 'id' => $model->id, 'status' => 'Activo'], [
                        'class' => 'btn btn-sm btn-success',
                        'data'  => ['method' => 'post', 'confirm' => '¿Marcar como Activo?'],
                    ]) ?>
                <?php endif; ?>
                <?php if (!$model->isInactivo()): ?>
                    <?= Html::a('🚫 Marcar Inactivo', ['update-status', 'id' => $model->id, 'status' => 'Inactivo'], [
                        'class' => 'btn btn-sm btn-danger',
                        'data'  => ['method' => 'post', 'confirm' => '¿Marcar como Inactivo? El docente perderá acceso y no aparecerá para consultores.'],
                    ]) ?>
                <?php endif; ?>
                <?php if ($model->isInactivo()): ?>
                    <?= Html::a('↩ Regresar a Registro', ['update-status', 'id' => $model->id, 'status' => 'Registro'], [
                        'class' => 'btn btn-sm btn-outline-warning',
                        'data'  => ['method' => 'post', 'confirm' => '¿Regresar a Registro para que el docente pueda editarlo?'],
                    ]) ?>
                <?php endif; ?>
                <?= Html::a('🗑 Eliminar', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-sm btn-outline-danger',
                    'data'  => ['confirm' => '¿Eliminar este expediente?', 'method' => 'post'],
                ]) ?>
            </div>

        <?php elseif (!$isConsultor && $model->isRegistro()): ?>
            <!-- espacio reservado — botón al fondo -->
        <?php endif; ?>
    </div>

    <!-- DATOS PERSONALES -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📋 Datos Personales</h5>
        </div>
        <div class="card-body">
            <?php if ($teaching): ?>
                <?= DetailView::widget([
                    'model'      => $teaching,
                    'attributes' => [
                        ['label' => 'Nombre Completo',      'value' => $teaching->nombreCompleto],
                        ['label' => 'Fecha de Nacimiento',  'value' => $teaching->born_date],
                        ['label' => 'Edad',                 'value' => $teaching->edad !== null ? $teaching->edad . ' años' : 'No especificada'],
                        'curp',
                        'gender:text:Género',
                        'email:email',
                        ['label' => 'Teléfono', 'value' => $teaching->phone_number],
                        'rfc:text:RFC',
                    ],
                ]) ?>
                <?php if (!$isConsultor && ($isAdmin || $model->isRegistro())): ?>
                    <?= Html::a('✏️ Editar datos personales', ['/teaching/update', 'id' => $teaching->id, 'record_id' => $recordId], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                <?php elseif (!$isConsultor && !$isAdmin): ?>
                    <p class="text-muted small mt-2"><i>Para modificar tus datos, solicítalo al administrador.</i></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted">Sin datos personales registrados.</p>
                <?php if (!$isConsultor): ?>
                    <?= Html::a('+ Registrar datos personales', ['/teaching/create', 'record_id' => $model->id], ['class' => 'btn btn-sm btn-primary']) ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- CONTACTOS DE EMERGENCIA -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0">🚨 Contactos de Emergencia</h5>
            <?php if (!$isConsultor && ($isAdmin || $model->isRegistro()) && count($emergencyContacts) < 2): ?>
                <?= Html::a('+ Agregar contacto', ['/emergency-contact/create', 'record_id' => $model->id], ['class' => 'btn btn-sm btn-dark']) ?>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if (!empty($emergencyContacts)): ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Parentesco</th>
                            <?php if (!$isConsultor): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emergencyContacts as $contact): ?>
                            <tr>
                                <td><?= Html::encode($contact->name) ?></td>
                                <td><?= Html::encode($contact->number_phone) ?></td>
                                <td><?= Html::encode($contact->parentesco) ?></td>
                                <?php if (!$isConsultor && ($isAdmin || $model->isRegistro())): ?>
                                    <td>
                                        <?= Html::a('✏️', ['/emergency-contact/update', 'id' => $contact->id, 'record_id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                        <?= Html::a('🗑', ['/emergency-contact/delete', 'id' => $contact->id], [
                                            'class' => 'btn btn-sm btn-outline-danger ms-1',
                                            'data'  => ['confirm' => '¿Eliminar este contacto?', 'method' => 'post'],
                                        ]) ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Sin contactos de emergencia registrados.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- DATOS LABORALES -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">💼 Datos Laborales</h5>
        </div>
        <div class="card-body">
            <?php if ($laborData): ?>
                <p><strong>Fecha de Ingreso:</strong> <?= Html::encode($laborData->entry_date) ?></p>
                <p><strong>Antigüedad:</strong>
                    <span class="badge bg-secondary"><?= $laborData->antiguedad ?? 'No calculada' ?></span>
                </p>
                <strong>Academias:</strong>
                <?php $academies = $laborData->academies; ?>
                <?php if (!empty($academies)): ?>
                    <ul class="list-group mt-2">
                        <?php foreach ($academies as $academy): ?>
                            <li class="list-group-item">
                                <span class="badge bg-secondary me-2"><?= $academy->id ?></span>
                                <?= Html::encode($academy->academy_name) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">Sin academias asignadas.</p>
                <?php endif; ?>
                <?php if (!$isConsultor && ($isAdmin || $model->isRegistro())): ?>
                    <?= Html::a('✏️ Editar datos laborales', ['/labor-data/update', 'id' => $laborData->id, 'record_id' => $recordId], ['class' => 'btn btn-sm btn-outline-success mt-3']) ?>
                <?php elseif (!$isConsultor && !$isAdmin): ?>
                    <p class="text-muted small mt-2"><i>Para modificar tus datos laborales, solicítalo al administrador.</i></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted">Sin datos laborales registrados.</p>
                <?php if (!$isConsultor): ?>
                    <?= Html::a('+ Registrar datos laborales', ['/labor-data/create', 'record_id' => $model->id], ['class' => 'btn btn-sm btn-success']) ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- DOCUMENTOS -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📁 Documentos</h5>
            <div class="d-flex flex-wrap gap-2">
                <?= Html::a('Descargar ZIP', ['download-documents', 'id' => $recordId], [
                    'class' => 'btn btn-sm btn-light' . (empty($documents) ? ' disabled' : ''),
                    'aria-disabled' => empty($documents) ? 'true' : null,
                    'onclick' => empty($documents) ? 'return false;' : null,
                ]) ?>
                <?php if (!$isConsultor && ($isAdmin || $model->isRegistro())): ?>
                    <?= Html::a('+ Subir documento', ['/document/create', 'record_id' => $recordId], ['class' => 'btn btn-sm btn-light']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($documents)): ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Archivo</th>
                            <th>Fecha de subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td><?= Html::encode($doc->documentType->type_name ?? '-') ?></td>
                                <td><?= Html::encode($doc->document_name ?? '-') ?></td>
                                <td><?= $doc->upload_date ?></td>
                                <td>
                                    <?= Html::a('⬇ Descargar', ['/document/download', 'id' => $doc->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?php if ($isAdmin): ?>
                                        <?= Html::a('🗑', ['/document/delete', 'id' => $doc->id], [
                                            'class' => 'btn btn-sm btn-outline-danger ms-1',
                                            'data'  => ['confirm' => '¿Eliminar?', 'method' => 'post'],
                                        ]) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Sin documentos subidos aún.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!$isAdmin && !$isConsultor && $model->isRegistro()): ?>
    <!-- BOTÓN FINALIZAR — siempre visible al fondo -->
    <div class="card border-success mb-4">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-1 text-success">¿Listo para finalizar tu expediente?</h5>
                <p class="mb-0 text-muted small">Asegúrate de haber completado tus datos personales, laborales, contactos de emergencia y documentos antes de finalizar.</p>
            </div>
            <?= Html::a('✅ Finalizar expediente', ['update-status', 'id' => $model->id, 'status' => 'Activo'], [
                'class' => 'btn btn-success btn-lg ms-3',
                'data'  => ['method' => 'post', 'confirm' => '¿Finalizar tu expediente? Una vez activo, el administrador gestionará cualquier cambio.'],
            ]) ?>
        </div>
    </div>
    <?php endif; ?>

</div>
