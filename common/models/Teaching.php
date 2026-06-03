<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "teaching".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $first_last_name
 * @property string|null $second_last_name
 * @property string|null $born_date
 * @property string|null $curp
 * @property string|null $gender
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $rfc
 * @property int|null $user_id
 *
 * @property EmergencyContact[] $emergencyContacts
 * @property Record[] $records
 * @property User $user
 */
class Teaching extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'teaching';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'first_last_name', 'second_last_name','born_date', 'curp', 'gender','email', 'phone_number','rfc', 'user_id'], 'required', 'message' => 'Este campo es obligatorio.'],
            [['name', 'first_last_name', 'second_last_name', 'born_date', 'curp', 'gender', 'email', 'phone_number', 'rfc', 'user_id'], 'default', 'value' => null],
            [['born_date'], 'safe'],
            [['user_id'], 'integer'],
            [['name', 'first_last_name', 'second_last_name'], 'string', 'max' => 100],
            [['curp'], 'string', 'max' => 18],
            [['gender'], 'string', 'max' => 10],
            [['email'], 'email', 'message' => 'Ingresa un correo electrónico válido.'],
            [['phone_number'], 'string', 'max' => 20],
            [['rfc'], 'string', 'max' => 13],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre(s)',
            'first_last_name' => 'Apellido paterno',
            'second_last_name' => 'Apellido materno',
            'born_date' => 'Fecha de nacimiento',
            'curp' => 'CURP',
            'gender' => 'Genero',
            'email' => 'Correo electronico',
            'phone_number' => 'Telefono',
            'rfc' => 'RFC',
            'user_id' => 'Usuario',
        ];
    }

    /**
     * Nombre completo del docente.
     */
    public function getNombreCompleto()
    {
        return trim($this->name . ' ' . $this->first_last_name . ' ' . $this->second_last_name);
    }

    /**
     * Edad calculada a partir de born_date.
     */
    public function getEdad()
    {
        if (!$this->born_date) return null;
        $nacimiento = new \DateTime($this->born_date);
        $hoy        = new \DateTime();
        return $nacimiento->diff($hoy)->y;
    }

    public function isProfileComplete(): bool
    {
        foreach (['name', 'first_last_name', 'second_last_name', 'born_date', 'curp', 'gender', 'email', 'phone_number', 'rfc'] as $attribute) {
            if (trim((string)$this->{$attribute}) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets query for [[EmergencyContacts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEmergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class, ['teaching_id' => 'id']);
    }

    /**
     * Gets query for [[Records]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecords()
    {
        return $this->hasMany(Record::class, ['teaching_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
