<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\LaborData $model */
/** @var common\models\Academy[] $academies */

$this->title = 'Datos Laborales #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Datos Laborales', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="labor-data-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data'  => ['confirm' => '¿Estás seguro?', 'method' => 'post'],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model'      => $model,
        'attributes' => [
            'id',
            [
                'label' => 'Fecha de Ingreso',
                'value' => $model->entry_date,
            ],
        ],
    ]) ?>

    <h4 class="mt-4">Academias</h4>
    <?php if (!empty($academies)): ?>
        <ul class="list-group">
            <?php foreach ($academies as $academy): ?>
                <li class="list-group-item">
                    <span class="badge bg-secondary me-2"><?= $academy->id ?></span>
                    <?= Html::encode($academy->academy_name) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">Sin academias asignadas.</p>
    <?php endif; ?>
</div>
