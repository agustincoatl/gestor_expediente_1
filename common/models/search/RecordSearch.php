<?php

namespace common\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use common\models\Record;

class RecordSearch extends Model
{
    public $id;
    public $teaching_id;
    public $labor_data_id;
    public $status;
    public $creation_date;
    public $teacher_name;

    public function rules(): array
    {
        return [
            [['id', 'teaching_id', 'labor_data_id'], 'integer'],
            [['status', 'creation_date', 'teacher_name'], 'safe'],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search($params): ActiveDataProvider
    {
        return $this->searchWithQuery($params, Record::find());
    }

    public function searchWithQuery($params, ActiveQuery $query): ActiveDataProvider
    {
        $query->joinWith(['teaching', 'estatus']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'record.id'            => $this->id,
            'record.teaching_id'   => $this->teaching_id,
            'record.labor_data_id' => $this->labor_data_id,
        ]);

        $query->andFilterWhere(['like', 'estatus_expediente.descripcion', $this->status]);

        $query->andFilterWhere([
            'or',
            ['like', 'teaching.name',            $this->teacher_name],
            ['like', 'teaching.first_last_name',  $this->teacher_name],
            ['like', 'teaching.second_last_name', $this->teacher_name],
        ]);

        return $dataProvider;
    }
}
