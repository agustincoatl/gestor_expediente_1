<?php

namespace common\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "document".
 */
class Document extends \yii\db\ActiveRecord
{
    /**
     * Atributo virtual para el archivo subido
     */
    public $archivo;

    public static function tableName()
    {
        return 'document';
    }

    public function rules()
    {
        return [
            [['record_id', 'document_type_id'], 'integer'],
            [['upload_date'], 'safe'],
            [['document_name', 'document_path'], 'string', 'max' => 255],
            [['record_id', 'document_type_id', 'document_name', 'document_path', 'upload_date'], 'default', 'value' => null],

            // Validación del archivo
            [['archivo'], 'file',
                'skipOnEmpty' => true,
                'extensions'  => 'pdf, doc, docx, jpg, jpeg, png',
                'maxSize'     => 1024 * 1024 * 10, // 10 MB
                'message'     => 'Formato no permitido. Use: pdf, doc, docx, jpg, png',
            ],

            [['document_type_id'], 'required', 'message' => 'Selecciona el tipo de documento'],

            [['document_type_id'], 'exist', 'skipOnError' => true,
                'targetClass'     => DocumentType::class,
                'targetAttribute' => ['document_type_id' => 'id'],
            ],
            [['record_id'], 'exist', 'skipOnError' => true,
                'targetClass'     => Record::class,
                'targetAttribute' => ['record_id' => 'id'],
            ],
            [['document_name', 'document_path'],'required','message' => 'Seleccione un documento.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'record_id'        => 'Expediente',
            'document_type_id' => 'Tipo de Documento',
            'document_name'    => 'Nombre del Archivo',
            'document_path'    => 'Ruta',
            'upload_date'      => 'Fecha de Subida',
            'archivo'          => 'Archivo',
        ];
    }

    public static function getUploadBaseRelativeDir(): string
    {
        return 'uploads/documents';
    }

    public static function getTeacherUploadRelativeDir(?Record $record): string
    {
        if ($record && $record->teaching) {
            $teacherName = $record->teaching->nombreCompleto ?: 'docente';
            return self::getUploadBaseRelativeDir() . '/docente_' . $record->teaching->id . '_' . self::sanitizePathSegment($teacherName, 70);
        }

        if ($record && $record->id) {
            return self::getUploadBaseRelativeDir() . '/expediente_' . $record->id;
        }

        return self::getUploadBaseRelativeDir() . '/sin_expediente';
    }

    public static function buildSafeUploadFileName(UploadedFile $file): string
    {
        $baseName = pathinfo($file->name, PATHINFO_FILENAME);
        $extension = strtolower((string)$file->extension);
        $safeBaseName = self::sanitizePathSegment($baseName, 90);
        $suffix = bin2hex(random_bytes(4));

        return date('Ymd_His') . '_' . $suffix . '_' . $safeBaseName . ($extension ? '.' . $extension : '');
    }

    public static function saveUploadedFileForRecord(UploadedFile $file, ?Record $record): ?array
    {
        $relativeDir = self::getTeacherUploadRelativeDir($record);
        $fullDir = Yii::getAlias('@backend/web/') . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);

        if (!is_dir($fullDir) && !mkdir($fullDir, 0755, true) && !is_dir($fullDir)) {
            return null;
        }

        $fileName = self::buildSafeUploadFileName($file);
        $fullPath = $fullDir . DIRECTORY_SEPARATOR . $fileName;

        if (!$file->saveAs($fullPath)) {
            return null;
        }

        return [
            'name' => $file->name,
            'path' => $relativeDir . '/' . $fileName,
        ];
    }

    public static function createZipForRecord(Record $record): ?array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('La extension ZIP de PHP no esta disponible.');
        }

        $documents = $record->documents;
        if (empty($documents)) {
            return null;
        }

        $teacherName = $record->teaching ? $record->teaching->nombreCompleto : 'expediente_' . $record->id;
        $zipName = 'expediente_' . $record->id . '_' . self::sanitizePathSegment($teacherName, 70) . '_documentos.zip';
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('expediente_' . $record->id . '_', true) . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        $count = 0;
        foreach ($documents as $index => $document) {
            if (!$document->document_path) {
                continue;
            }

            $filePath = Yii::getAlias('@backend/web/') . $document->document_path;
            if (!is_file($filePath)) {
                continue;
            }

            $entryName = self::buildZipEntryName($document, $index + 1);
            if ($zip->addFile($filePath, $entryName)) {
                $count++;
            }
        }

        $zip->close();

        if ($count === 0) {
            if (is_file($zipPath)) {
                unlink($zipPath);
            }
            return null;
        }

        return [
            'path' => $zipPath,
            'name' => $zipName,
            'count' => $count,
        ];
    }

    private static function buildZipEntryName(Document $document, int $index): string
    {
        $type = $document->documentType ? $document->documentType->type_name : 'documento';
        $name = $document->document_name ?: basename((string)$document->document_path);
        $baseName = pathinfo($name, PATHINFO_FILENAME);
        $extension = strtolower((string)pathinfo($name, PATHINFO_EXTENSION));

        if ($extension === '' && $document->document_path) {
            $extension = strtolower((string)pathinfo($document->document_path, PATHINFO_EXTENSION));
        }

        $entry = str_pad((string)$index, 2, '0', STR_PAD_LEFT)
            . '_' . self::sanitizePathSegment($type, 50)
            . '_' . self::sanitizePathSegment($baseName, 90);

        return $entry . ($extension ? '.' . $extension : '');
    }

    private static function sanitizePathSegment($value, int $maxLength = 80): string
    {
        $value = trim((string)$value);
        $value = preg_replace('/[^A-Za-z0-9_-]+/', '_', $value);
        $value = trim($value, '_-');
        $value = strtolower($value);
        $value = substr($value, 0, $maxLength);
        $value = trim($value, '_-');

        return $value !== '' ? $value : 'sin_nombre';
    }

    public function getDocumentType()
    {
        return $this->hasOne(DocumentType::class, ['id' => 'document_type_id']);
    }

    public function getRecord()
    {
        return $this->hasOne(Record::class, ['id' => 'record_id']);
    }
}
