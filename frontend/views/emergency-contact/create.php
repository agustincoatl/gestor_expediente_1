<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\EmergencyContact $model */

$this->title = 'Agregar Contacto de Emergencia';
$this->params['breadcrumbs'][] = ['label' => 'Expedientes', 'url' => ['/record/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="emergency-contact-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model'    => $model,
        'isAdmin'  => $isAdmin,
        'recordId' => $recordId,
    ]) ?>
</div>