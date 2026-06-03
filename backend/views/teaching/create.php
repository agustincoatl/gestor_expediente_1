<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Teaching $model */

$this->title = 'Crear docente';
$this->params['breadcrumbs'][] = ['label' => 'Teachings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="teaching-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
