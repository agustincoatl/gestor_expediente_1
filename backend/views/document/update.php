<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Document $model */
/** @var array $documentTypes */
/** @var common\models\Record|null $record */
/** @var bool $isAdmin */
/** @var int|null $recordId */

$this->title = 'Actualizar Documento';
$this->params['breadcrumbs'][] = ['label' => 'Documentos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="document-update">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model'         => $model,
        'documentTypes' => $documentTypes,
        'record'        => $record ?? null,
        'isAdmin'       => $isAdmin,
        'recordId'      => $recordId ?? $model->record_id,
    ]) ?>
</div>
