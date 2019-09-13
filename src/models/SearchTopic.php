<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * This is the search model class for table "{{%ticket_topic}}".
 */
class SearchTopic extends Topic
{
    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            ['new_ticket_status', 'integer'],
            ['name', 'string', 'max' => 255],
            [['new_ticket_assign_to', 'created_by', 'updated_by'], 'string', 'max' => 64],
            ['status', 'boolean'],
            ['created_at', 'datetime', 'timestampAttribute' => 'created_at'],
            ['updated_at', 'datetime', 'timestampAttribute' => 'updated_at']
        ];
    }/**
 * {@inheritDoc}
 */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param null|integer|string $userId
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Topic::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'new_ticket_assign_to' => $this->new_ticket_assign_to,
            'new_ticket_status' => $this->new_ticket_status,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
