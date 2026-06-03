<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\User;
use frontend\models\ChangePasswordForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use common\models\Teaching;
use common\models\Record;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['logout', 'signup', 'change-password'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'change-password'],
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
            'captcha' => [
                'class'           => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Página de inicio: landing para invitados, redirección por rol para autenticados.
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirectByRole();
        }

        return $this->render('index');
    }

    /**
     * Login: autentica y redirige por rol.
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirectByRole();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if (Yii::$app->user->identity->role_id === User::ROL_ADMIN) {
                return $this->redirectByRole();
            }
            if (Yii::$app->user->identity->must_change_password) {
                return $this->redirect(['/site/change-password']);
            }
            return $this->redirectByRole();
        }

        $model->password = '';

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Redirige al destino correcto según el rol del usuario autenticado.
     *
     * - Admin    → backend
     * - Docente  → su expediente (o al registro si aún no tiene)
     * - Consultor→ lista de expedientes
     */
    protected function redirectByRole()
    {
        $user = Yii::$app->user->identity;

        if ($user->role_id === User::ROL_ADMIN) {
            return $this->redirect($this->getBackendUrl());
        }

        if ($user->role_id === User::ROL_DOCENTE) {
            $teaching = Teaching::findOne(['user_id' => $user->id]);
            if (!$teaching) {
                return $this->redirect(['/teaching/create']);
            }
            if (!$teaching->isProfileComplete()) {
                Yii::$app->session->setFlash('warning', 'Completa tus datos personales antes de continuar.');
                return $this->redirect(['/teaching/update', 'id' => $teaching->id]);
            }
            return $this->redirect(['/record/index']);
        }

        // Consultor
        return $this->redirect(['/record/index']);
    }

    protected function getBackendUrl(): string
    {
        if (!empty(Yii::$app->params['backendUrl'])) {
            return Yii::$app->params['backendUrl'];
        }

        $baseUrl = Yii::$app->request->baseUrl;
        if (strpos($baseUrl, '/frontend/web') !== false) {
            return str_replace('/frontend/web', '/backend/web', $baseUrl) . '/index.php?r=site%2Findex';
        }

        return '/gestor_expediente_1/backend/web/index.php?r=site%2Findex';
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Cuenta creada correctamente. Ya puedes iniciar sesión.');
            return $this->redirect(['/site/login']);
        }

        return $this->render('signup', ['model' => $model]);
    }

    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Revisa tu correo para continuar.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'No se pudo procesar la solicitud.');
        }

        return $this->render('requestPasswordResetToken', ['model' => $model]);
    }

    public function actionChangePassword()
    {
        $model = new ChangePasswordForm(Yii::$app->user->identity);

        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Yii::$app->session->setFlash('success', 'Contraseña actualizada correctamente.');
            return $this->redirect(['/site/index']);
        }

        return $this->render('changePassword', ['model' => $model]);
    }

    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'Nueva contraseña guardada correctamente.');
            return $this->redirect(['/site/login']);
        }

        return $this->render('resetPassword', ['model' => $model]);
    }

    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($model->verifyEmail()) {
            Yii::$app->session->setFlash('success', '¡Correo verificado! Ya puedes iniciar sesión.');
            return $this->redirect(['/site/login']);
        }
        Yii::$app->session->setFlash('error', 'No se pudo verificar el token.');
        return $this->goHome();
    }

    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Correo de verificación reenviado.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'No se pudo reenviar el correo.');
        }

        return $this->render('resendVerificationEmail', ['model' => $model]);
    }
}
