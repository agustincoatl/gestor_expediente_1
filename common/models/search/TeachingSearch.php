<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;
use common\models\Teaching;

class TeachingSearch extends Teaching
{
    public $teacher_name;

    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['name', 'first_last_name', 'second_last_name', 'teacher_name', 'born_date', 'curp', 'gender', 'email', 'phone_number', 'rfc'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param array $params
     * @param ActiveQuery|null $query Query pre-filtrado (ej: solo registros del docente logueado)
     */
    public function search($params, $query = null)
    {
        if ($query === null) {
            $query = Teaching::find();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'       => $this->id,
            'born_date'=> $this->born_date,
            'user_id'  => $this->user_id,
        ]);

        if (trim((string)$this->teacher_name) !== '') {
            $query->andFilterWhere([
                'or',
                ['like', 'name', $this->teacher_name],
                ['like', 'first_last_name', $this->teacher_name],
                ['like', 'second_last_name', $this->teacher_name],
                ['like', new Expression("CONCAT(name, ' ', first_last_name, ' ', second_last_name)"), $this->teacher_name],
            ]);
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'first_last_name', $this->first_last_name])
            ->andFilterWhere(['like', 'second_last_name', $this->second_last_name])
            ->andFilterWhere(['like', 'curp', $this->curp])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'phone_number', $this->phone_number])
            ->andFilterWhere(['like', 'rfc', $this->rfc]);

        return $dataProvider;
    }
}
