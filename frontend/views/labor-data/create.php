<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\LaborData $model */
/** @var common\models\Academy[] $allAcademies */
/** @var int|null $academyId */
/** @var bool $isAdmin */

$this->title = 'Datos Laborales';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="py-3" style="max-width:700px; margin:0 auto;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-1">Datos Laborales</h5>
            <p class="text-muted small mb-4">Registra tu fecha de ingreso y academia para actualizar el checklist.</p>

            <?= $this->render('_form', [
                'model' => $model,
                'allAcademies' => $allAcademies,
                'academyId' => $academyId,
                'isAdmin' => $isAdmin,
            ]) ?>
        </div>
    </div>
</div>
