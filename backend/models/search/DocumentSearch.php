<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use common\models\Document;

class DocumentSearch extends Document
{
    public function rules()
    {
        return [
            [['id', 'record_id', 'document_type_id'], 'integer'],
            [['document_name', 'document_path', 'upload_date'], 'safe'],
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
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'               => $this->id,
            'record_id'        => $this->record_id,
            'document_type_id' => $this->document_type_id,
            'upload_date'      => $this->upload_date,
        ]);

        $query->andFilterWhere(['like', 'document_name', $this->document_name])
              ->andFilterWhere(['like', 'document_path', $this->document_path]);

        return $dataProvider;
    }
}
