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
 * This is the search model class for table "{{%ticket__ticket}}".
 */
class SearchTicket extends Ticket
{
    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [['id', 'source_id', 'due_date', 'priority'], 'integer'],
            [['topic_id'], 'each', 'rule' => ['integer']],
            ['description', 'string'],
            [['assigned_to', 'created_by', 'updated_by', 'closed_by'], 'string', 'max' => 64],
            ['subject', 'string', 'max' => 255],
            [
                ['source_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Source::class,
                'targetAttribute' => ['source_id' => 'id']
            ],
            [
                ['topic_id'],
                'each',
                'rule' => [
                    'exist',
                    'skipOnError' => true,
                    'targetClass' => Topic::class,
                    'targetAttribute' => ['topic_id' => 'id']
                ]
            ],
            [
                'priority',
                'in',
                'range' => [self::PRIORITY_LOW, self::PRIORITY_NORMAL, self::PRIORITY_HIGH, self::PRIORITY_EMERGENCY]
            ],
            ['created_at', 'datetime', 'timestampAttribute' => 'created_at'],
            ['updated_at', 'datetime', 'timestampAttribute' => 'updated_at'],
            ['closed_at', 'datetime', 'timestampAttribute' => 'closed_at'],
            [
                'status',
                'each',
                'rule' => [
                    'in',
                    'range' => [
                        self::STATUS_LATE,
                        self::STATUS_OPEN,
                        self::STATUS_ASSIGNED,
                        self::STATUS_IN_PROGRESS,
                        self::STATUS_RESOLVED
                    ]
                ]
            ]
        ];
    }

    /**
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
    public function search($params, $userId = null)
    {
        $query = Ticket::find()
            ->alias('t');

        if (\Yii::$app->user->can('ticketAdministrator')) {
            // Can see everything, no filters applied
        } elseif (\Yii::$app->user->can('ticketAgent')) {
            // Agent: show only tickets of topics where agent is responsible, own and assigned tickets
            $query
                ->innerJoin(['ta' => '{{%ticket__topic_agent}}'], 't.topic_id = ta.topic_id')
                ->andWhere([
                    'or',
                    ['{{ta}}.[[agent_id]]' => \Yii::$app->user->id],
                    ['created_by' => \Yii::$app->user->id],
                    ['assigned_to' => \Yii::$app->user->id]
                ]);
        } elseif ($userId) {
            // normal user: show only own and assigned tickets
            $query->andWhere([
                'or',
                ['created_by' => $userId],
                ['assigned_to' => $userId]
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'assigned_to' => $this->assigned_to,
            'source_id' => $this->source_id,
            'topic_id' => $this->topic_id,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'priority' => $this->priority,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'closed_by' => $this->closed_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'closed_at' => $this->closed_at,
        ]);

        $query
            ->andFilterWhere(['like', 'subject', $this->subject])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
