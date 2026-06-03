<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "academy".
 *
 * @property int $id
 * @property string $academy_name
 *
 * @property LaborAcademy[] $laborAcademies
 * @property LaborData[] $labors
 */
class Academy extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'academy';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['academy_name'], 'required'],
            [['academy_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'academy_name' => 'Academia',
        ];
    }

    /**
     * Gets query for [[LaborAcademies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLaborAcademies()
    {
        return $this->hasMany(LaborAcademy::class, ['academy_id' => 'id']);
    }

    /**
     * Gets query for [[Labors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLabors()
    {
        return $this->hasMany(LaborData::class, ['id' => 'labor_id'])->viaTable('labor_academy', ['academy_id' => 'id']);
    }

}
