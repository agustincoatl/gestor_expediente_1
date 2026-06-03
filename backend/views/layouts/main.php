<?php

/** @var \yii\web\View $this */
/** @var string $content */

use backend\assets\AppAsset;
use common\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);

$isGuest     = Yii::$app->user->isGuest;
$isAdmin     = !$isGuest && Yii::$app->user->identity->role_id == 1;
$isDocente   = !$isGuest && Yii::$app->user->identity->role_id == 2;
$isConsultor = !$isGuest && Yii::$app->user->identity->role_id == 3;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="es" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> — Expedientes ITSV</title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header>
    <?php
    $brandLogo = Html::img('@web/img/log.png', [
        'alt' => 'ITSV',
        'style' => 'height: 34px; width: auto;',
        'class' => 'me-2',
    ]);

    NavBar::begin([
        'brandLabel' => $brandLogo . '<span class="fw-light opacity-75">Admin</span>',
        'brandUrl'   => Yii::$app->homeUrl,
        'options'    => [
            'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top shadow-sm',
        ],
    ]);

    $menuItems = [];

    if ($isAdmin) {
        $menuItems[] = ['label' => 'Inicio', 'url' => ['/site/index']];
        $menuItems[] = [
            'label' => 'Catálogos',
            'items' => [
                ['label' => 'Roles',              'url' => ['/role/index']],
                ['label' => 'Academias',           'url' => ['/academy/index']],
                ['label' => 'Tipos de Documento',  'url' => ['/document-type/index']],
                ['label' => '---'],
                ['label' => '👤 Usuarios',         'url' => ['/user/index']],
            ],
        ];
        $menuItems[] = [
            'label' => 'Expedientes',
            'items' => [
                ['label' => 'Docentes',        'url' => ['/teaching/index']],
                //['label' => 'Datos Laborales', 'url' => ['/labor-data/index']],
                ['label' => 'Expedientes',     'url' => ['/record/index']],
                ['label' => 'Documentos',      'url' => ['/document/index']],
            ],
        ];
    } elseif ($isDocente) {
        $menuItems[] = ['label' => 'Datos Personales', 'url' => ['/teaching/index']];
        $menuItems[] = ['label' => 'Datos Laborales',  'url' => ['/labor-data/index']];
        $menuItems[] = ['label' => 'Documentos',       'url' => ['/document/index']];
    } elseif ($isConsultor) {
        $menuItems[] = ['label' => 'Expedientes', 'url' => ['/record/index']];
    }

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
        'items'   => $menuItems,
    ]);

    if ($isGuest) {
        echo Html::tag('div',
            Html::a('Iniciar sesión', ['/site/login'],
                ['class' => 'btn btn-link login text-decoration-none text-white']),
            ['class' => 'd-flex']
        );
    } else {
        $username = Html::encode(Yii::$app->user->identity->username);
        echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex align-items-center gap-2'])
            . Html::tag('span', $username, ['class' => 'text-white-50 small d-none d-md-inline'])
            . Html::submitButton('Cerrar sesión', ['class' => 'btn btn-outline-light btn-sm ms-2'])
            . Html::endForm();
    }

    NavBar::end();
    ?>
</header>

<main role="main" class="flex-shrink-0">
    <div class="container mt-5 pt-3">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer class="footer mt-auto py-3 text-muted bg-light border-top">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="small">&copy; <?= date('Y') ?> Instituto Tecnológico Superior de Valladolid</span>
        <span class="small text-muted">Panel Administrativo</span>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
