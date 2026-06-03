<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\DocumentType $model */

$this->title = 'Actualizar tipo de documento: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tipos de documento', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="document-type-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
