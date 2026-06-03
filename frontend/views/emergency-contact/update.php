<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\EmergencyContact $model */

$this->title = 'Editar Contacto de Emergencia';
$this->params['breadcrumbs'][] = ['label' => 'Expedientes', 'url' => ['/record/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="emergency-contact-update">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model'    => $model,
        'isAdmin'  => $isAdmin,
        'recordId' => null,
    ]) ?>
</div>