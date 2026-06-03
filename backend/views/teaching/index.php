<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var common\models\search\TeachingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $isAdmin */

$this->title = 'Docentes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="teaching-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($isAdmin): ?>
<!--     <p>
        <?= Html::a('+ Registrar docente', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
    <?php endif; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'teacher_name',
                'label' => 'Nombre completo',
                'value' => function ($model) {
                    return trim($model->name . ' ' . $model->first_last_name . ' ' . $model->second_last_name);
                },
            ],
            'born_date:text:Fecha de nacimiento',
            [
                'attribute' => 'email',
                'format' => 'email',
                'label' => 'Correo',
            ],
            'phone_number:text:Teléfono',
            [
                'class'      => ActionColumn::class,
                'template'   => '{view} {update}',
                'urlCreator' => function ($action, $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
            ],
        ],
    ]); ?>

</div>
