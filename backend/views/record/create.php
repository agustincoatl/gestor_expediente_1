<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Record $model */

$this->title = 'Crear expediente';
$this->params['breadcrumbs'][] = ['label' => 'Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="record-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
