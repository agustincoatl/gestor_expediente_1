<?php

namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $repeatPassword;

    private ?User $_user = null;

    public function __construct(User $user, $config = [])
    {
        $this->_user = $user;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['currentPassword', 'newPassword', 'repeatPassword'], 'required'],
            ['currentPassword', 'validateCurrentPassword'],
            ['newPassword', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            ['newPassword', 'compare', 'compareAttribute' => 'currentPassword', 'operator' => '!=', 'message' => 'La nueva contraseña debe ser diferente a la actual.'],
            ['repeatPassword', 'compare', 'compareAttribute' => 'newPassword', 'message' => 'Las contraseñas no coinciden.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'currentPassword' => 'Contraseña actual',
            'newPassword' => 'Nueva contraseña',
            'repeatPassword' => 'Confirmar contraseña',
        ];
    }

    public function validateCurrentPassword($attribute): void
    {
        if (!$this->hasErrors() && !$this->_user->validatePassword($this->{$attribute})) {
            $this->addError($attribute, 'La contraseña actual no es correcta.');
        }
    }

    public function changePassword(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->_user->setPassword($this->newPassword);
        $this->_user->generateAuthKey();
        $this->_user->password_reset_token = null;
        $this->_user->must_change_password = 0;

        return $this->_user->save(false);
    }
}
