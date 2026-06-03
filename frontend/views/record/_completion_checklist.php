<?php

use yii\helpers\Html;

/** @var array $checklist */
/** @var bool $showActions */

$showActions = $showActions ?? true;
$percent = (int)($checklist['percent'] ?? 0);
$done = (int)($checklist['done'] ?? 0);
$total = (int)($checklist['total'] ?? 0);
$pending = (int)($checklist['pending'] ?? 0);
?>

<div class="record-checklist mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1">Checklist de expediente</h2>
            <p class="text-muted mb-0">
                Los datos que llegaron por API aparecen marcados; completa solo lo pendiente.
            </p>
        </div>
        <div class="text-end">
            <span class="badge bg-<?= $pending === 0 ? 'success' : 'primary' ?> fs-6">
                <?= $done ?> de <?= $total ?> completos
            </span>
            <div class="small text-muted mt-1"><?= $percent ?>%</div>
        </div>
    </div>

    <div class="row g-3">
        <?php foreach ($checklist['sections'] as $section): ?>
            <?php
            $sectionDone = true;
            foreach ($section['items'] as $item) {
                if (!$item['done']) {
                    $sectionDone = false;
                    break;
                }
            }
            ?>
            <div class="col-12 col-lg-6">
                <div class="border rounded p-3 h-100 bg-white">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <h3 class="h6 mb-0"><?= Html::encode($section['title']) ?></h3>
                        <span class="badge bg-<?= $sectionDone ? 'success' : 'warning text-dark' ?>">
                            <?= $sectionDone ? 'Completo' : 'Pendiente' ?>
                        </span>
                    </div>

                    <ul class="list-unstyled mb-3">
                        <?php foreach ($section['items'] as $item): ?>
                            <li class="d-flex align-items-start gap-2 py-1">
                                <span class="badge rounded-pill bg-<?= $item['done'] ? 'success' : 'light text-muted border' ?>">
                                    <?= $item['done'] ? '&check;' : '&nbsp;' ?>
                                </span>
                                <span class="flex-grow-1">
                                    <span class="<?= $item['done'] ? '' : 'fw-semibold' ?>">
                                        <?= Html::encode($item['label']) ?>
                                    </span>
                                    <?php if ($item['done'] && trim((string)($item['value'] ?? '')) !== ''): ?>
                                        <span class="text-muted small d-block"><?= Html::encode($item['value']) ?></span>
                                    <?php endif; ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($showActions && !$sectionDone): ?>
                        <?= Html::a(Html::encode($section['actionLabel']), $section['url'], [
                            'class' => 'btn btn-sm btn-outline-primary',
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($showActions && !empty($checklist['nextUrl']) && $pending > 0): ?>
        <div class="mt-3">
            <?= Html::a('Continuar con pendientes', $checklist['nextUrl'], ['class' => 'btn btn-primary']) ?>
        </div>
    <?php endif; ?>
</div>
