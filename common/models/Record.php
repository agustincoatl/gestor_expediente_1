<?php

namespace common\models;

/**
 * @property int         $id
 * @property int|null    $teaching_id
 * @property int         $estatus_id
 * @property string|null $creation_date
 * @property int|null    $labor_data_id
 *
 * @property EstatusExpediente $estatus
 * @property Document[]        $documents
 * @property LaborData         $laborData
 * @property Teaching          $teaching
 */
class Record extends \yii\db\ActiveRecord
{
    public static function tableName(): string
    {
        return 'record';
    }

    public function rules(): array
    {
        return [
            [['teaching_id', 'labor_data_id'], 'integer'],
            [['estatus_id'], 'integer'],
            [['estatus_id'], 'default', 'value' => EstatusExpediente::REGISTRO],
            [['creation_date'], 'safe'],
            [['estatus_id'], 'exist', 'targetClass' => EstatusExpediente::class, 'targetAttribute' => ['estatus_id' => 'id']],
            [['labor_data_id'], 'exist', 'skipOnError' => true, 'targetClass' => LaborData::class, 'targetAttribute' => ['labor_data_id' => 'id']],
            [['teaching_id'], 'exist', 'skipOnError' => true, 'targetClass' => Teaching::class, 'targetAttribute' => ['teaching_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'            => 'ID',
            'teaching_id'   => 'Docente',
            'estatus_id'    => 'Estado',
            'creation_date' => 'Fecha de creación',
            'labor_data_id' => 'Datos Laborales',
        ];
    }

    // ── Helpers de estado ────────────────────────────────────────
    public function isRegistro(): bool  { return $this->estatus_id === EstatusExpediente::REGISTRO; }
    public function isActivo(): bool    { return $this->estatus_id === EstatusExpediente::ACTIVO; }
    public function isInactivo(): bool  { return $this->estatus_id === EstatusExpediente::INACTIVO; }

    public function getEstatusDescripcion(): string
    {
        return $this->estatus->descripcion ?? '—';
    }

    // ── Relaciones ───────────────────────────────────────────────
    public function getEstatus()
    {
        return $this->hasOne(EstatusExpediente::class, ['id' => 'estatus_id']);
    }

    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['record_id' => 'id']);
    }

    public function getLaborData()
    {
        return $this->hasOne(LaborData::class, ['id' => 'labor_data_id']);
    }

    public function getTeaching()
    {
        return $this->hasOne(Teaching::class, ['id' => 'teaching_id']);
    }
}
