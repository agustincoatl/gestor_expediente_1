<?php

/** @var yii\web\View $this */

use common\models\User;
use yii\bootstrap5\Html;

$this->title = 'Panel de Administración';
$this->params['breadcrumbs'][] = $this->title;

// Solo admin debe llegar aquí; si alguien más llega, redirigir
if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role_id !== User::ROL_ADMIN) {
    return $this->redirect(['/record/index']);
}
?>

<style>
.stat-card {
    border-radius: .875rem;
    border: 1px solid #e9ecef;
    transition: box-shadow .2s;
}
.stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.07); }
.stat-icon {
    width: 48px; height: 48px;
    border-radius: .625rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem;
}
.quick-link {
    display: flex; align-items: center; gap: .75rem;
    padding: .85rem 1rem;
    border-radius: .625rem;
    border: 1px solid #e9ecef;
    text-decoration: none;
    color: #212529;
    transition: background .15s, border-color .15s;
}
.quick-link:hover { background: #f8f9fa; border-color: #ced4da; color: #212529; }
.quick-link .ql-icon { font-size: 1.2rem; }
</style>

<div class="py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Panel de Administración</h4>
            <p class="text-muted small mb-0">Gestor de Expedientes Docentes — ITSV</p>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
            Administrador
        </span>
    </div>

    <!-- Accesos rápidos -->
    <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.72rem;letter-spacing:.08em;">Accesos rápidos</h6>
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <a href="<?= \yii\helpers\Url::to(['/record/index']) ?>" class="quick-link">
                <span class="ql-icon">📋</span>
                <div>
                    <div class="fw-semibold small">Expedientes</div>
                    <div class="text-muted" style="font-size:.75rem;">Ver todos</div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="<?= \yii\helpers\Url::to(['/teaching/index']) ?>" class="quick-link">
                <span class="ql-icon">👤</span>
                <div>
                    <div class="fw-semibold small">Docentes</div>
                    <div class="text-muted" style="font-size:.75rem;">Datos personales</div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="<?= \yii\helpers\Url::to(['/user/index']) ?>" class="quick-link">
                <span class="ql-icon">🔑</span>
                <div>
                    <div class="fw-semibold small">Usuarios</div>
                    <div class="text-muted" style="font-size:.75rem;">Gestionar cuentas</div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="<?= \yii\helpers\Url::to(['/document/index']) ?>" class="quick-link">
                <span class="ql-icon">📎</span>
                <div>
                    <div class="fw-semibold small">Documentos</div>
                    <div class="text-muted" style="font-size:.75rem;">Archivos subidos</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Catálogos -->
    <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.72rem;letter-spacing:.08em;">Catálogos</h6>
    <div class="row g-3">
        <div class="col-sm-6 col-lg-4">
            <a href="<?= \yii\helpers\Url::to(['/academy/index']) ?>" class="quick-link">
                <span class="ql-icon">🏫</span>
                <div>
                    <div class="fw-semibold small">Academias</div>
                    <div class="text-muted" style="font-size:.75rem;">Gestionar academias</div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4">
            <a href="<?= \yii\helpers\Url::to(['/document-type/index']) ?>" class="quick-link">
                <span class="ql-icon">🗂️</span>
                <div>
                    <div class="fw-semibold small">Tipos de Documento</div>
                    <div class="text-muted" style="font-size:.75rem;">Configurar catálogo</div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4">
            <a href="<?= \yii\helpers\Url::to(['/role/index']) ?>" class="quick-link">
                <span class="ql-icon">🛡️</span>
                <div>
                    <div class="fw-semibold small">Roles</div>
                    <div class="text-muted" style="font-size:.75rem;">Configurar roles</div>
                </div>
            </a>
        </div>
    </div>
</div>
