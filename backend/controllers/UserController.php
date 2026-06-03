<?php

namespace backend\controllers;

use common\models\Teaching;
use common\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class UserController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'activate' => ['POST'],
                    'deactivate' => ['POST'],
                    'delete' => ['POST'],
                    'import-docentes' => ['GET', 'POST'],
                    'reset-password' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $busqueda = Yii::$app->request->get('busqueda', '');

        $query = User::find()->where(['!=', 'role_id', User::ROL_ADMIN]);

        if ($busqueda !== '') {
            $query->andWhere(['or',
                ['like', 'username', $busqueda],
                ['like', 'email', $busqueda],
            ]);
        }

        $users = $query->orderBy(['status' => SORT_ASC, 'created_at' => SORT_DESC])->all();

        return $this->render('index', [
            'users' => $users,
            'busqueda' => $busqueda,
        ]);
    }

    public function actionImportDocentes()
    {
        $request = Yii::$app->request;
        $baseUrl = $request->post('baseUrl', Yii::$app->params['docenteApi.baseUrl'] ?? '');
        $apiKey = $request->post('apiKey', Yii::$app->params['docenteApi.apiKey'] ?? '');
        $resetExistingPasswords = (bool)$request->post('resetExistingPasswords', false);
        $result = null;

        if ($request->isPost) {
            $docentes = $this->fetchDocentesFromApi($baseUrl, $apiKey);
            if ($docentes === null) {
                Yii::$app->session->setFlash('error', 'No se pudo consultar la API de docentes. Verifica la URL y la API key.');
            } else {
                $result = $this->importDocentes($docentes, $resetExistingPasswords);
                Yii::$app->session->setFlash(
                    'success',
                    "Importacion terminada: {$result['created']} creados, {$result['updated']} actualizados, {$result['skipped']} omitidos."
                );
            }
        }

        return $this->render('import-docentes', [
            'baseUrl' => $baseUrl,
            'apiKey' => $apiKey,
            'resetExistingPasswords' => $resetExistingPasswords,
            'result' => $result,
        ]);
    }

    public function actionActivate($id)
    {
        $user = $this->findModel($id);
        $user->status = User::STATUS_ACTIVE;
        $user->verification_token = null;
        $user->save(false);

        Yii::$app->session->setFlash('success', "Cuenta de {$user->username} activada.");
        return $this->redirect(['index', 'busqueda' => Yii::$app->request->get('busqueda', '')]);
    }

    public function actionResetPassword($id)
    {
        $user = $this->findModel($id);
        $password = Yii::$app->request->post('password', Yii::$app->params['docenteApi.defaultPassword'] ?? 'Docente2026');

        if (!in_array((int)$user->role_id, [User::ROL_DOCENTE, User::ROL_CONSULTOR], true)) {
            Yii::$app->session->setFlash('error', 'La restauracion de contrasena solo esta disponible para docentes y consultores.');
            return $this->redirect(['index', 'busqueda' => Yii::$app->request->get('busqueda', '')]);
        }

        if (strlen($password) < Yii::$app->params['user.passwordMinLength']) {
            Yii::$app->session->setFlash('error', 'La contrasena debe tener al menos ' . Yii::$app->params['user.passwordMinLength'] . ' caracteres.');
            return $this->redirect(['index', 'busqueda' => Yii::$app->request->get('busqueda', '')]);
        }

        $user->setPassword($password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        $user->verification_token = null;
        $user->password_reset_token = null;
        $user->must_change_password = 1;
        $user->save(false);

        Yii::$app->session->setFlash('success', "Contrasena de {$user->username} actualizada. El usuario debera cambiarla al iniciar sesion.");
        return $this->redirect(['index', 'busqueda' => Yii::$app->request->get('busqueda', '')]);
    }

    public function actionDeactivate($id)
    {
        $user = $this->findModel($id);
        $user->status = User::STATUS_INACTIVE;
        $user->save(false);

        Yii::$app->session->setFlash('warning', "Cuenta de {$user->username} desactivada.");
        return $this->redirect(['index', 'busqueda' => Yii::$app->request->get('busqueda', '')]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('danger', 'Usuario eliminado.');
        return $this->redirect(['index']);
    }

    private function fetchDocentesFromApi(string $baseUrl, string $apiKey): ?array
    {
        $url = $this->buildDocentesApiUrl($baseUrl);
        $verifySsl = (bool)(Yii::$app->params['docenteApi.verifySsl'] ?? true);

        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'x-api-key: ' . $apiKey,
                    'Accept: application/json',
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => $verifySsl,
                CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            ]);
            $response = curl_exec($curl);
            $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
                $data = json_decode($response, true);
                if (is_array($data)) {
                    return $data;
                }
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "x-api-key: {$apiKey}\r\nAccept: application/json\r\n",
                'timeout' => 30,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => $verifySsl,
                'verify_peer_name' => $verifySsl,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }

    private function buildDocentesApiUrl(string $url): string
    {
        $url = rtrim(trim($url), '/');
        if (preg_match('~/docentes$~', $url)) {
            return $url;
        }

        return $url . '/docentes';
    }

    private function generarPasswordSegura(): string
    {
        $mayus = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $minus = 'abcdefghjkmnpqrstuvwxyz';
        $numeros = '23456789';
        $simbolos = '@#$!%*?&';

        $password = $mayus[random_int(0, strlen($mayus) - 1)];
        $password .= $minus[random_int(0, strlen($minus) - 1)];
        $password .= $numeros[random_int(0, strlen($numeros) - 1)];
        $password .= $simbolos[random_int(0, strlen($simbolos) - 1)];

        $todos = $mayus . $minus . $numeros . $simbolos;
        for ($i = 4; $i < 12; $i++) {
            $password .= $todos[random_int(0, strlen($todos) - 1)];
        }

        return str_shuffle($password);
    }

    private function importDocentes(array $docentes, bool $resetExistingPasswords): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [], 'passwords' => []];

        foreach ($docentes as $docente) {
            $username = trim((string)($docente['nombre_usuario'] ?? ''));
            if ($username === '') {
                $result['skipped']++;
                $result['errors'][] = 'Docente omitido: sin nombre_usuario.';
                continue;
            }

            $email = trim((string)($docente['email'] ?? ''));
            if ($email === '') {
                $email = $username . '@sin-correo.local';
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $user = User::findOne(['username' => $username]) ?: User::findOne(['email' => $email]);
                $isNewUser = $user === null;
                $password = null;

                if ($isNewUser) {
                    $password = $this->generarPasswordSegura();
                    $user = new User();
                    $user->username = $username;
                    $user->setPassword($password);
                    $user->generateAuthKey();
                    $user->status = User::STATUS_ACTIVE;
                    $user->role_id = User::ROL_DOCENTE;
                    $user->must_change_password = 1;
                } elseif ($resetExistingPasswords) {
                    $password = $this->generarPasswordSegura();
                    $user->setPassword($password);
                    $user->generateAuthKey();
                    $user->must_change_password = 1;
                }

                if ($user->username === null || $user->username === '') {
                    $user->username = $username;
                }
                $user->email = $email;
                $user->role_id = User::ROL_DOCENTE;
                $user->status = User::STATUS_ACTIVE;
                $user->verification_token = null;

                if (!$user->save(false)) {
                    throw new \RuntimeException('No se pudo guardar el usuario ' . $username);
                }

                $teaching = Teaching::findOne(['user_id' => $user->id]) ?: new Teaching();
                $teaching->user_id = $user->id;
                $this->fillTeachingFromApi($teaching, $docente, $email);

                if (!$teaching->save(false)) {
                    throw new \RuntimeException('No se pudieron guardar los datos del docente ' . $username);
                }

                if ($password !== null) {
                    $result['passwords'][] = [
                        'username' => $user->username,
                        'email' => $user->email,
                        'password' => $password,
                        'nuevo' => $isNewUser,
                    ];
                    // $this->enviarBienvenida($user, $password); // descomentar para enviar correos
                }

                $transaction->commit();
                $isNewUser ? $result['created']++ : $result['updated']++;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                $result['skipped']++;
                $result['errors'][] = $username . ': ' . $e->getMessage();
            }
        }

        return $result;
    }

    private function fillTeachingFromApi(Teaching $teaching, array $docente, string $email): void
    {
        if ($teaching->isNewRecord) {
            $teaching->name = '';
            $teaching->first_last_name = '';
            $teaching->second_last_name = '';
            $teaching->curp = '';
            $teaching->gender = '';
            $teaching->email = $email;
            $teaching->phone_number = '';
            $teaching->rfc = '';
        }

        $map = [
            'name' => 'nombres',
            'first_last_name' => 'apellido_paterno',
            'second_last_name' => 'apellido_materno',
            'born_date' => 'fecha_nacimiento',
            'email' => 'email',
            'phone_number' => 'telefono',
            'rfc' => 'rfc',
        ];

        foreach ($map as $attribute => $apiField) {
            $value = $docente[$apiField] ?? null;
            if ($value !== null && trim((string)$value) !== '') {
                $teaching->{$attribute} = trim((string)$value);
            } elseif ($teaching->isNewRecord && $attribute === 'email') {
                $teaching->{$attribute} = $email;
            }
        }
    }

    protected function findModel($id)
    {
        $model = User::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Usuario no encontrado.');
        }
        return $model;
    }
}
