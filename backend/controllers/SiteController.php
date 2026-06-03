<?php

namespace backend\controllers;

use common\models\LoginForm;
use common\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller — Backend (solo administradores)
 */
class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow'   => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Dashboard principal del administrador.
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role_id !== User::ROL_ADMIN) {
            return $this->redirectToFrontendLogin();
        }

        $this->clearPortalDocenteFlash();
        return $this->render('index');
    }

    /**
     * Login del backend (solo para administradores).
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            if (Yii::$app->user->identity->role_id !== User::ROL_ADMIN) {
                return $this->redirectToFrontendLogin();
            }
            $this->clearPortalDocenteFlash();
            return $this->redirect(['/site/index']);
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if (Yii::$app->user->identity->role_id !== User::ROL_ADMIN) {
                return $this->redirectToFrontendLogin();
            }
            $this->clearPortalDocenteFlash();
            return $this->redirect(['/site/index']);
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Logout.
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    protected function redirectToFrontendLogin()
    {
        Yii::$app->user->logout();
        return $this->redirect($this->getFrontendLoginUrl());
    }

    protected function clearPortalDocenteFlash(): void
    {
        if (Yii::$app->session->getFlash('warning') === 'Ingresa desde el portal docente.') {
            Yii::$app->session->removeFlash('warning');
        }
    }

    protected function getFrontendLoginUrl(): string
    {
        if (!empty(Yii::$app->params['frontendLoginUrl'])) {
            return Yii::$app->params['frontendLoginUrl'];
        }

        $baseUrl = Yii::$app->request->baseUrl;
        if (strpos($baseUrl, '/backend/web') !== false) {
            return str_replace('/backend/web', '/frontend/web', $baseUrl) . '/index.php?r=site%2Flogin';
        }

        return '/gestor_expediente_1/frontend/web/index.php?r=site%2Flogin';
    }
}
