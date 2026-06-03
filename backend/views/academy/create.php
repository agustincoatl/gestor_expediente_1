<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Academy $model */

$this->title = 'Crear academia';
$this->params['breadcrumbs'][] = ['label' => 'Academies', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="academy-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
