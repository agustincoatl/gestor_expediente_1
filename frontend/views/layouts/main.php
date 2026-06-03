<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\models\User;
use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);
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
    $brandLogo = Html::img('@web/img/logo.png', [
        'alt' => 'ITSV',
        'style' => 'height: 34px; width: auto;',
        'class' => 'me-2',
    ]);

    NavBar::begin([
        'brandLabel' => $brandLogo . '<span class="fw-light opacity-75">Expediente</span>',
        'brandUrl'   => Yii::$app->homeUrl,
        'options'    => ['class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top shadow-sm'],
    ]);

    $menuItems = [];

    if (!Yii::$app->user->isGuest) {
        $user = Yii::$app->user->identity;

        if ($user->role_id === User::ROL_DOCENTE) {
            //$menuItems[] = ['label' => 'Mi Expediente',    'url' => ['/record/index']];
            //$menuItems[] = ['label' => 'Datos Personales', 'url' => ['/teaching/index']];
           // $menuItems[] = ['label' => 'Datos Laborales',  'url' => ['/labor-data/index']];
            //$menuItems[] = ['label' => 'Documentos',       'url' => ['/document/index']];
        }

        if ($user->role_id === User::ROL_CONSULTOR) {
            $menuItems[] = ['label' => 'Expedientes', 'url' => ['/record/index']];
        }
    }

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
        'items'   => $menuItems,
    ]);

    if (Yii::$app->user->isGuest) {
        echo Html::tag('div',
            Html::a('Iniciar sesión', ['/site/login'],
                ['class' => 'btn btn-outline-light btn-sm me-2'])
            . Html::a('Registrarse', ['/site/signup'],
                ['class' => 'btn btn-primary btn-sm']),
            ['class' => 'd-flex align-items-center']
        );
    } else {
        $username = Html::encode(Yii::$app->user->identity->username);
        echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex align-items-center gap-2'])
            . Html::tag('span', $username, ['class' => 'text-white-50 small d-none d-md-inline'])
            . Html::a('Cambiar contraseña', ['/site/change-password'], ['class' => 'btn btn-outline-light btn-sm ms-2'])
            . Html::submitButton('Cerrar sesión', ['class' => 'btn btn-outline-light btn-sm ms-2'])
            . Html::endForm();
    }

    NavBar::end();
    ?>
</header>

<main role="main" class="flex-shrink-0 mt-5 pt-3">
    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => $this->params['breadcrumbs'] ?? [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer class="footer mt-auto py-3 text-muted bg-light border-top">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="small">&copy; <?= date('Y') ?> Instituto Tecnológico Superior de Valladolid</span>
        <span class="small text-muted">Gestor de Expedientes Docentes</span>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
