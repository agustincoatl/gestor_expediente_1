<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "labor_data".
 *
 * @property int $id
 * @property string|null $entry_date
 *
 * @property Academy[] $academies
 * @property LaborAcademy[] $laborAcademies
 * @property Record[] $records
 */
class LaborData extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'labor_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['entry_date'], 'default', 'value' => null],
            [['entry_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entry_date' => 'Fecha de ingreso',
        ];
    }

    /**
     * Gets query for [[Academies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAcademies()
    {
        return $this->hasMany(Academy::class, ['id' => 'academy_id'])->viaTable('labor_academy', ['labor_id' => 'id']);
    }

    /**
     * Gets query for [[LaborAcademies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLaborAcademies()
    {
        return $this->hasMany(LaborAcademy::class, ['labor_id' => 'id']);
    }

    /**
     * Gets query for [[Records]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecords()
    {
        return $this->hasMany(Record::class, ['labor_data_id' => 'id']);
    }

    /**
     * Antigüedad laboral calculada a partir de entry_date.
     * Retorna un string legible: "X años, Y meses"
     */
    public function getAntiguedad()
    {
        if (!$this->entry_date) return null;
        $ingreso = new \DateTime($this->entry_date);
        $hoy     = new \DateTime();
        $diff    = $ingreso->diff($hoy);
        if ($diff->y === 0 && $diff->m === 0) {
            return 'Menos de un mes';
        }
        $partes = [];
        if ($diff->y > 0) $partes[] = $diff->y . ' ' . ($diff->y === 1 ? 'año' : 'años');
        if ($diff->m > 0) $partes[] = $diff->m . ' ' . ($diff->m === 1 ? 'mes' : 'meses');
        return implode(', ', $partes);
    }

    /**
     * Años completos de antigüedad (numérico, para cálculos).
     */
    public function getAnosAntiguedad()
    {
        if (!$this->entry_date) return null;
        $ingreso = new \DateTime($this->entry_date);
        return $ingreso->diff(new \DateTime())->y;
    }

}
