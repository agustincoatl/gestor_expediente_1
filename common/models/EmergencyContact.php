<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "emergency_contact".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $number_phone
 * @property string|null $parentesco
 * @property int $teaching_id
 *
 * @property Teaching $teaching
 */
class EmergencyContact extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'emergency_contact';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'number_phone', 'parentesco'], 'default', 'value' => null],
            [['teaching_id'], 'required'],
            [['teaching_id'], 'integer'],
            [['name'], 'string', 'max' => 255,],
            [['number_phone'], 'string', 'max' => 20],
            [['parentesco'], 'string', 'max' => 100],
            [['teaching_id'], 'exist', 'skipOnError' => true, 'targetClass' => Teaching::class, 'targetAttribute' => ['teaching_id' => 'id']],
            [['name', 'number_phone', 'parentesco'], 'required','message' => 'Este campo es obligatorio.'],
            [['number_phone'], 'match', 'pattern' => '/^\d{10}$/', 
            'message' => 'El teléfono debe contener exactamente 10 números.'
        ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre',
            'number_phone' => 'Telefono',
            'parentesco' => 'Parentesco',
            'teaching_id' => 'Docente',
        ];
    }

    /**
     * Gets query for [[Teaching]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTeaching()
    {
        return $this->hasOne(Teaching::class, ['id' => 'teaching_id']);
    }

}
