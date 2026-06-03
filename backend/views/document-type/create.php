<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\DocumentType $model */

$this->title = 'Crear tipo de documento';
$this->params['breadcrumbs'][] = ['label' => 'Tipos de documento', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-type-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
