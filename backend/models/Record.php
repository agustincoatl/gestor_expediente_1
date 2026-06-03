<?php

namespace backend\models;

use common\models\Document;
use Yii;

/**
 * This is the model class for table "record".
 *
 * @property int $id
 * @property int|null $teaching_id
 * @property string|null $status
 * @property string|null $creation_date
 * @property int|null $labor_data_id
 *
 * @property Document[] $documents
 * @property LaborData $laborData
 * @property Teaching $teaching
 */
class Record extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['teaching_id', 'status', 'creation_date', 'labor_data_id'], 'default', 'value' => null],
            [['teaching_id', 'labor_data_id'], 'integer'],
            [['creation_date'], 'safe'],
            [['status'], 'string', 'max' => 50],
            [['labor_data_id'], 'exist', 'skipOnError' => true, 'targetClass' => LaborData::class, 'targetAttribute' => ['labor_data_id' => 'id']],
            [['teaching_id'], 'exist', 'skipOnError' => true, 'targetClass' => Teaching::class, 'targetAttribute' => ['teaching_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'teaching_id' => 'Docente',
            'status' => 'Estado',
            'creation_date' => 'Fecha de creacion',
            'labor_data_id' => 'Datos laborales',
        ];
    }

    /**
     * Gets query for [[Documents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['record_id' => 'id']);
    }

    /**
     * Gets query for [[LaborData]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLaborData()
    {
        return $this->hasOne(LaborData::class, ['id' => 'labor_data_id']);
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
