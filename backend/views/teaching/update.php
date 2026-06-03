<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Teaching $model */

$this->title = 'Actualizar docente: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Teachings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="teaching-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
