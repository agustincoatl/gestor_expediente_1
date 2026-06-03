<?php

use common\models\User;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User[] $users */
/** @var string $busqueda */

$this->title = 'Gestion de usuarios';
$this->params['breadcrumbs'][] = $this->title;

$roles = [
    User::ROL_DOCENTE => 'Docente',
    User::ROL_CONSULTOR => 'Consultor',
];

$statusBadge = function (int $status): string {
    return $status === User::STATUS_ACTIVE
        ? '<span class="badge bg-success">Activo</span>'
        : '<span class="badge bg-warning text-dark">Pendiente</span>';
};
?>

<div class="user-index">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-3">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>
            <p class="text-muted mb-0">Activa, desactiva o elimina cuentas de docentes y consultores.</p>
        </div>
        <?= Html::a('Importar docentes API', ['import-docentes'], ['class' => 'btn btn-primary']) ?>
    </div>

    <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 mb-4']) ?>
        <div class="col-md-5">
            <?= Html::textInput('busqueda', $busqueda, [
                'class' => 'form-control',
                'placeholder' => 'Buscar por nombre de usuario o correo...',
                'autofocus' => true,
            ]) ?>
        </div>
        <div class="col-auto">
            <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary']) ?>
            <?php if ($busqueda !== ''): ?>
                <?= Html::a('Limpiar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php endif; ?>
        </div>
    <?= Html::endForm() ?>

    <?php if ($busqueda !== ''): ?>
        <p class="text-muted small">
            <?= count($users) ?> resultado(s) para <strong>"<?= Html::encode($busqueda) ?>"</strong>
        </p>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <div class="alert alert-info">No se encontraron usuarios<?= $busqueda !== '' ? ' con ese criterio' : '' ?>.</div>
    <?php else: ?>
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th>Restaurar contrasena</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= Html::encode($user->username) ?></td>
                    <td><?= Html::encode($user->email) ?></td>
                    <td>
                        <span class="badge <?= $user->role_id === User::ROL_DOCENTE ? 'bg-primary' : 'bg-secondary' ?>">
                            <?= $roles[$user->role_id] ?? 'Desconocido' ?>
                        </span>
                    </td>
                    <td><?= $statusBadge($user->status) ?></td>
                    <td><?= date('d/m/Y H:i', $user->created_at) ?></td>
                    <td>
                        <?php if (in_array((int)$user->role_id, [User::ROL_DOCENTE, User::ROL_CONSULTOR], true)): ?>
                            <?= Html::beginForm(['/user/reset-password', 'id' => $user->id, 'busqueda' => $busqueda], 'post', ['class' => 'd-flex gap-1']) ?>
                                <?= Html::passwordInput('password', Yii::$app->params['docenteApi.defaultPassword'] ?? 'Docente2026', [
                                    'class' => 'form-control form-control-sm',
                                    'style' => 'width: 140px',
                                    'aria-label' => 'Nueva contrasena',
                                    'autocomplete' => 'new-password',
                                ]) ?>
                                <?= Html::submitButton('Restablecer contrasena', [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'data' => ['confirm' => "Cambiar la contrasena de {$user->username}?"],
                                ]) ?>
                            <?= Html::endForm() ?>
                        <?php else: ?>
                            <span class="text-muted small">No disponible</span>
                        <?php endif; ?>
                    </td>
                    <td class="d-flex gap-2 flex-wrap">
                        <?php if ($user->status === User::STATUS_INACTIVE): ?>
                            <?= Html::beginForm(['/user/activate', 'id' => $user->id, 'busqueda' => $busqueda], 'post') ?>
                                <?= Html::submitButton('Activar', [
                                    'class' => 'btn btn-sm btn-success',
                                    'data' => ['confirm' => "Activar la cuenta de {$user->username}?"],
                                ]) ?>
                            <?= Html::endForm() ?>
                        <?php else: ?>
                            <?= Html::beginForm(['/user/deactivate', 'id' => $user->id, 'busqueda' => $busqueda], 'post') ?>
                                <?= Html::submitButton('Desactivar', [
                                    'class' => 'btn btn-sm btn-outline-warning',
                                    'data' => ['confirm' => "Desactivar la cuenta de {$user->username}?"],
                                ]) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>

                        <?= Html::beginForm(['/user/delete', 'id' => $user->id], 'post') ?>
                            <?= Html::submitButton('Eliminar', [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data' => ['confirm' => "Eliminar permanentemente la cuenta de {$user->username}? Esta accion no se puede deshacer."],
                            ]) ?>
                        <?= Html::endForm() ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
