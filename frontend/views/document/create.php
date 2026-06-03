<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Document $model */
/** @var array $documentTypes */
/** @var common\models\Record|null $record */
/** @var int|null $recordId */
/** @var bool $hasDocuments */
/** @var bool $isAdmin */

$this->title = 'Subir Documento';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="document-create py-3" style="max-width: 720px; margin: 0 auto;">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1"><?= Html::encode($this->title) ?></h1>
            <p class="text-muted small mb-0">Sube un documento para actualizar el checklist de tu expediente.</p>
        </div>
        <?php if ($recordId): ?>
            <?= Html::a('Volver al Expediente', ['/record/view', 'id' => $recordId], [
                'class' => 'btn btn-sm btn-outline-secondary',
            ]) ?>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <?= $this->render('_form', [
                'model' => $model,
                'documentTypes' => $documentTypes,
                'record' => $record,
                'isAdmin' => $isAdmin,
                'recordId' => $recordId ?? null,
            ]) ?>
        </div>
    </div>
</div>
