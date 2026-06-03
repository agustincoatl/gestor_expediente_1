<?php

namespace common\models;
use Yii;

/**
 * This is the model class for table "labor_academy".
 *
 * @property int $labor_id
 * @property int $academy_id
 *
 * @property Academy $academy
 * @property LaborData $labor
 */
class LaborAcademy extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'labor_academy';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['labor_id', 'academy_id'], 'required'],
            [['labor_id', 'academy_id'], 'integer'],
            [['labor_id', 'academy_id'], 'unique', 'targetAttribute' => ['labor_id', 'academy_id']],
            [['labor_id'], 'exist', 'skipOnError' => true, 'targetClass' => LaborData::class, 'targetAttribute' => ['labor_id' => 'id']],
            [['academy_id'], 'exist', 'skipOnError' => true, 'targetClass' => Academy::class, 'targetAttribute' => ['academy_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'labor_id' => 'Datos laborales',
            'academy_id' => 'Academia',
        ];
    }

    /**
     * Gets query for [[Academy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAcademy()
    {
        return $this->hasOne(Academy::class, ['id' => 'academy_id']);
    }

    /**
     * Gets query for [[Labor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLabor()
    {
        return $this->hasOne(LaborData::class, ['id' => 'labor_id']);
    }

}
