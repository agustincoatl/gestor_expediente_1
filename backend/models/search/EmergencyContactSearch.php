<?php

namespace backend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\EmergencyContact;

/**
 * EmergencyContactSearch represents the model behind the search form of `backend\models\EmergencyContact`.
 */
class EmergencyContactSearch extends EmergencyContact
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'teaching_id'], 'integer'],
            [['name', 'number_phone', 'parentesco'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
/*     public function search($params, $formName = null)
    {
        $query = EmergencyContact::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([    
            'id' => $this->id,
            'teaching_id' => $this->teaching_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'number_phone', $this->number_phone])
            ->andFilterWhere(['like', 'parentesco', $this->parentesco]);

        return $dataProvider;
    
    } */
    public function searchWithQuery($params, $query, $formName = null)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'teaching_id' => $this->teaching_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'number_phone', $this->number_phone])
            ->andFilterWhere(['like', 'parentesco', $this->parentesco]);

        return $dataProvider;
    }
    
}
