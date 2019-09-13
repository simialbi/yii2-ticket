<?php

namespace simialbi\yii2\ticket\models;

use simialbi\yii2\models\UserInterface;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%ticket_topic}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $new_ticket_assign_to
 * @property integer $new_ticket_status
 * @property boolean $status
 * @property string $created_by
 * @property string $updated_by
 * @property integer|string $created_at
 * @property integer|string $updated_at
 *
 * @property-read UserInterface[] $agents
 * @property-read UserInterface $newTicketAgent
 * @property-read Ticket[] $tickets
 */
class Topic extends ActiveRecord
{
    private $_agents;

    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '{{%ticket_topic}}';
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            ['new_ticket_status', 'integer'],
            ['new_ticket_status', 'default', 'value' => Ticket::STATUS_OPEN],
            ['name', 'string', 'max' => 255],
            ['new_ticket_assign_to', 'string', 'max' => 64],
            ['new_ticket_assign_to', 'default'],
            ['status', 'boolean'],
            ['status', 'default', 'value' => true],

            ['name', 'required'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('simialbi/ticket/model/topic', 'ID'),
            'name' => Yii::t('simialbi/ticket/model/topic', 'Name'),
            'new_ticket_assign_to' => Yii::t('simialbi/ticket/model/topic', 'New Ticket Assign To'),
            'new_ticket_status' => Yii::t('simialbi/ticket/model/topic', 'New Ticket Status'),
            'status' => Yii::t('simialbi/ticket/model/topic', 'Status'),
            'created_by' => Yii::t('simialbi/ticket/model/topic', 'Created By'),
            'updated_by' => Yii::t('simialbi/ticket/model/topic', 'Updated By'),
            'created_at' => Yii::t('simialbi/ticket/model/topic', 'Created At'),
            'updated_at' => Yii::t('simialbi/ticket/model/topic', 'Updated At'),
        ];
    }

    /**
     * Get assigned agents
     * @return UserInterface[]
     */
    public function getAgents()
    {
        if (!$this->_agents) {
            $users = ArrayHelper::index(call_user_func([Yii::$app->user->identity, 'findIdentities']), 'id');
            $query = new Query();
            $query
                ->select(['agent_id'])
                ->from('{{%ticket_topic_agent}}')
                ->where(['topic_id' => $this->id]);

            $ids = $query->column();
            $this->_agents = ArrayHelper::filter($users, $ids);
        }
        return $this->_agents;
    }

    /**
     * Get assigned new ticket agent
     * @return UserInterface
     */
    public function getNewTicketAgent()
    {
        return call_user_func([Yii::$app->user->identityClass, 'findIdentity'], $this->new_ticket_assign_to);
    }

    /**
     * Get associated tickets
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::class, ['topic_id' => 'id']);
    }
}
