<?php

use common\models\Record;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var common\models\search\RecordSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var bool $isAdmin */

$user        = Yii::$app->user->identity;
$isConsultor = $user->role_id == 3;

$this->title = 'Expedientes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="record-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($isAdmin): ?>
<!--     <p>
        <?= Html::a('+ Crear expediente', ['create'], ['class' => 'btn btn-success']) ?>
    </p> -->
    <?php endif; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel'  => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'teacher_name',
                'label' => 'Docente',
                'value' => function ($model) {
                    return $model->teaching->nombreCompleto;
                    },
            ],
/*             [
                'label'   => 'Docente',
                'value'   => function($model) {
                    $t = $model->teaching;
                    if (!$t) return 'Sin datos';
                    return trim($t->name . ' ' . $t->first_last_name . ' ' . $t->second_last_name);
                },
            ], */
            [
                'attribute' => 'estatus_id',
                'label'     => 'Estado',
                'format'    => 'raw',
                'value'     => function($model) {
                    $desc   = $model->getEstatusDescripcion();
                    $colors = [
                        'Registro'  => 'secondary',
                        'Activo'    => 'success',
                        'Inactivo'  => 'danger',
                    ];
                    $color = $colors[$desc] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . Html::encode($desc) . '</span>';
                },
            ],
            'creation_date:text:Fecha de creación',
            [
                'class' => ActionColumn::className(),
                'template' => '{view}',
                'urlCreator' => function ($action, $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
            ],
        ],
    ]); ?>

</div>
