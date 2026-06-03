<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use backend\models\LaborData;

class LaborDataSearch extends LaborData
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['entry_date'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        return $this->searchWithQuery($params, LaborData::find(), $formName);
    }

    public function searchWithQuery($params, ActiveQuery $query, $formName = null)
    {
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'         => $this->id,
            'entry_date' => $this->entry_date,
        ]);

        return $dataProvider;
    }
}