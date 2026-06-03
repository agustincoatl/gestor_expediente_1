<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Teaching $model */

$this->title = 'Datos Personales';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="py-3" style="max-width:700px; margin:0 auto;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1">Datos Personales</h5>
            <p class="text-muted small mb-4">Completa tu informacion personal para actualizar el checklist de tu expediente.</p>

            <?= $this->render('_form', ['model' => $model]) ?>
        </div>
    </div>
</div>
