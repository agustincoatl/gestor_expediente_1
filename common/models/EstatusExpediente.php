<?php

namespace common\models;

use Yii;

/**
 * Modelo para la tabla `estatus_expediente`.
 *
 * @property int    $id
 * @property string $descripcion
 *
 * @property Record[] $records
 */
class EstatusExpediente extends \yii\db\ActiveRecord
{
    // ── Constantes de estado ─────────────────────────────────────
    const REGISTRO = 1;
    const ACTIVO   = 2;
    const INACTIVO = 3;

    public static function tableName(): string
    {
        return 'estatus_expediente';
    }

    public function rules(): array
    {
        return [
            [['descripcion'], 'required'],
            [['descripcion'], 'string', 'max' => 50],
            [['descripcion'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id'          => 'ID',
            'descripcion' => 'Descripción',
        ];
    }

    // ── Relaciones ───────────────────────────────────────────────
    public function getRecords()
    {
        return $this->hasMany(Record::class, ['estatus_id' => 'id']);
    }
}