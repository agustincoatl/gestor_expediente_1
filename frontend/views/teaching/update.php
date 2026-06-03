<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Teaching $model */
/** @var bool $expedienteActivo */
/** @var array $camposEditables */

$this->title = 'Actualizar Datos Personales';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="teaching-update">
    <?= $this->render('_form', [
        'model'            => $model,
        'expedienteActivo' => $expedienteActivo ?? false,
        'camposEditables'  => $camposEditables  ?? [],
    ]) ?>
</div>
