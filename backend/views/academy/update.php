<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Academy $model */

$this->title = 'Actualizar academia: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Academies', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="academy-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
