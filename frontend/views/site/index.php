<?php

/** @var yii\web\View $this */

use common\models\User;
use yii\bootstrap5\Html;

$this->title = 'Gestor de Expedientes Docentes';

// Usuario autenticado → redirigir según rol (igual que actionIndex del controller)
if (!Yii::$app->user->isGuest) {
    $user = Yii::$app->user->identity;
    if ($user->role_id === User::ROL_DOCENTE || $user->role_id === User::ROL_CONSULTOR) {
        return $this->redirect(['/record/index']);
    }
}
?>

<style>
.landing-hero {
    min-height: calc(100vh - 56px);
    background: linear-gradient(135deg, #1a1f36 0%, #2d3561 60%, #1e3a5f 100%);
    display: flex;
    align-items: center;
    margin-top: -56px;
    padding-top: 56px;
}
.landing-card {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.13);
    border-radius: 1.25rem;
    backdrop-filter: blur(10px);
    padding: 2.5rem;
}
.badge-inst {
    background: rgba(255,255,255,0.15);
    color: #c8d6f5;
    font-size: .78rem;
    letter-spacing: .06em;
    border-radius: 50px;
    padding: .35em .85em;
    border: 1px solid rgba(255,255,255,0.2);
}
.feature-icon {
    width: 48px; height: 48px;
    background: rgba(99,179,237,0.15);
    border-radius: .75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    margin-bottom: .75rem;
}
.btn-cta-primary {
    background: #3b82f6;
    border: none;
    color: #fff;
    font-weight: 600;
    padding: .65rem 1.75rem;
    border-radius: .6rem;
    transition: background .2s, transform .15s;
}
.btn-cta-primary:hover { background: #2563eb; color:#fff; transform: translateY(-1px); }
.btn-cta-outline {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.35);
    color: #e2e8f0;
    font-weight: 500;
    padding: .65rem 1.75rem;
    border-radius: .6rem;
    transition: background .2s;
}
.btn-cta-outline:hover { background: rgba(255,255,255,0.1); color:#fff; }
</style>

<div class="landing-hero">
    <div class="container py-5">
        <div class="row align-items-center g-5">

            <!-- Texto principal -->
            <div class="col-lg-6">
                <span class="badge-inst mb-3 d-inline-block">
                    Instituto Tecnológico Superior de Valladolid
                </span>
                <h1 class="display-5 fw-bold text-white lh-sm mb-3">
                    Gestor de<br>Expedientes Docentes
                </h1>
                <p class="text-white-50 fs-5 mb-4">
                    Plataforma centralizada para el registro, seguimiento y consulta
                    de expedientes del personal académico.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?= Html::a('Iniciar sesión', ['/site/login'],  ['class' => 'btn btn-cta-primary']) ?>
                    <?= Html::a('Crear cuenta',   ['/site/signup'], ['class' => 'btn btn-cta-outline']) ?>
                </div>
            </div>

            <!-- Tarjeta de características -->
            <div class="col-lg-5 offset-lg-1">
                <div class="landing-card">
                    <div class="row g-4">
                        <div class="col-6">
                            <div class="feature-icon">📋</div>
                            <h6 class="text-white fw-semibold mb-1">Expediente digital</h6>
                            <p class="text-white-50 small mb-0">Toda la información del docente en un solo lugar.</p>
                        </div>
                        <div class="col-6">
                            <div class="feature-icon">📎</div>
                            <h6 class="text-white fw-semibold mb-1">Documentos</h6>
                            <p class="text-white-50 small mb-0">Adjunta y organiza documentos de soporte.</p>
                        </div>
                        <div class="col-6">
                            <div class="feature-icon">🔒</div>
                            <h6 class="text-white fw-semibold mb-1">Acceso por rol</h6>
                            <p class="text-white-50 small mb-0">Docente, consultor y administrador con permisos diferenciados.</p>
                        </div>
                        <div class="col-6">
                            <div class="feature-icon">📊</div>
                            <h6 class="text-white fw-semibold mb-1">Seguimiento</h6>
                            <p class="text-white-50 small mb-0">Monitorea el estado de cada expediente en tiempo real.</p>
                        </div>
                    </div>

                    <hr style="border-color:rgba(255,255,255,.1); margin:1.5rem 0;">

                    <p class="text-white-50 small mb-0 text-center">
                        ¿Docente? Accede con tu correo <strong class="text-white-50">@valladolid.tecnm.mx</strong>
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>
