<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $role_id;

    const DOMINIO_DOCENTE = '@valladolid.tecnm.mx';
    const ROL_DOCENTE     = 2;
    const ROL_CONSULTOR   = 3;

    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique',
                'targetClass' => '\common\models\User',
                'message'     => 'Este usuario ya está en uso.',
            ],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique',
                'targetClass' => '\common\models\User',
                'message'     => 'Este correo ya está registrado.',
            ],

            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],

            ['role_id', 'integer'],
            ['role_id', 'in', 'range' => [self::ROL_DOCENTE, self::ROL_CONSULTOR]],
        ];
    }

    public function esDocente(): bool
    {
        return str_ends_with(strtolower(trim($this->email)), self::DOMINIO_DOCENTE);
    }

    /**
     * Crea la cuenta con STATUS_ACTIVE directo.
     * El bloqueo de campos se controla por el estatus del EXPEDIENTE (Record),
     * no por el status del usuario.
     * - Sin expediente     → formulario completo (isRegistro / null)
     * - Expediente Activo  → solo campos permitidos (isActivo)
     * - Expediente Inactivo→ bloqueado (isInactivo)
     */
    public function signup(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email    = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        // Activo de inmediato — el control de campos va por estatus del expediente
        $user->status = User::STATUS_ACTIVE;

        $user->role_id = $this->esDocente()
            ? self::ROL_DOCENTE
            : ($this->role_id ?? self::ROL_CONSULTOR);

        return $user->save() ? $user : null;
    }
}
