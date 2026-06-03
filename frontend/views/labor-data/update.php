<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\LaborData $model */

$this->title = 'Actualizar Datos Laborales';
$this->params['breadcrumbs'][] = ['label' => 'Datos Laborales', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="labor-data-update">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model'        => $model,
        'allAcademies' => $allAcademies,
        'academyId'    => $academyId,
        'isAdmin'      => isset($isAdmin) ? $isAdmin : false,
    ]) ?>
</div>
