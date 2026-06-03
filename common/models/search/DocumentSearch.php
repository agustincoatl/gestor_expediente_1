<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use common\models\Document;

class DocumentSearch extends Document
{
    public $teacher_name;

    public function rules()
    {
        return [
            [['id', 'record_id', 'document_type_id'], 'integer'],
            [['document_name', 'document_path', 'upload_date', 'teacher_name'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        return $this->searchWithQuery($params, Document::find(), $formName);
    }

    public function searchWithQuery($params, ActiveQuery $query, $formName = null)
    {
        $query->joinWith(['record.teaching', 'documentType']);

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'document.id' => $this->id,
            'document.record_id' => $this->record_id,
            'document.document_type_id' => $this->document_type_id,
            'document.upload_date' => $this->upload_date,
        ]);

        $query->andFilterWhere(['like', 'document.document_name', $this->document_name])
              ->andFilterWhere(['like', 'document.document_path', $this->document_path]);

        if (trim((string)$this->teacher_name) !== '') {
            $query->andFilterWhere([
                'or',
                ['like', 'teaching.name', $this->teacher_name],
                ['like', 'teaching.first_last_name', $this->teacher_name],
                ['like', 'teaching.second_last_name', $this->teacher_name],
                ['like', new Expression("CONCAT(teaching.name, ' ', teaching.first_last_name, ' ', teaching.second_last_name)"), $this->teacher_name],
                ['like', 'teaching.email', $this->teacher_name],
            ]);
        }

        $query->orderBy([
            'teaching.name' => SORT_ASC,
            'teaching.first_last_name' => SORT_ASC,
            'teaching.second_last_name' => SORT_ASC,
            'document.upload_date' => SORT_DESC,
        ]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'teacher_name' => 'Docente',
        ]);
    }
}
