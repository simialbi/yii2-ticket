<?php

namespace simialbi\yii2\ticket\models;

use simialbi\yii2\models\UserInterface;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%ticket__topic}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $new_ticket_assign_to
 * @property integer $new_ticket_status
 * @property string $template
 * @property boolean $status
 * @property string $on_new_ticket
 * @property string $on_ticket_update
 * @property string $on_ticket_assignment
 * @property string $on_ticket_resolution
 * @property string $on_ticket_comment
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
    const BEHAVIOR_SMS = 'sms';
    const BEHAVIOR_MAIL = 'mail';

    private $_agents;

    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '{{%ticket__topic}}';
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
            ['template', 'string'],
            [['new_ticket_assign_to', 'template'], 'default'],
            ['status', 'boolean'],
            ['status', 'default', 'value' => true],
            [
                ['on_new_ticket', 'on_ticket_assignment', 'on_ticket_update', 'on_ticket_resolution', 'on_ticket_comment'],
                'in',
                'range' => [self::BEHAVIOR_MAIL, self::BEHAVIOR_SMS]
            ],
            [
                ['on_new_ticket', 'on_ticket_assignment', 'on_ticket_update', 'on_ticket_resolution', 'on_ticket_comment'],
                'default'
            ],

            ['name', 'required'],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_by', 'updated_by'],
                    self::EVENT_BEFORE_UPDATE => 'updated_by',
                ],
                'preserveNonEmptyValues' => true
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    self::EVENT_BEFORE_UPDATE => 'updated_at'
                ]
            ]
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
            'template' => Yii::t('simialbi/ticket/model/topic', 'Template'),
            'status' => Yii::t('simialbi/ticket/model/topic', 'Status'),
            'on_new_ticket' => Yii::t(
                'simialbi/ticket/model/topic',
                'Notification on {event}',
                ['event' => Yii::t('simialbi/ticket/model/topic', 'new ticket')]
            ),
            'on_ticket_update' => Yii::t(
                'simialbi/ticket/model/topic',
                'Notification on {event}',
                ['event' => Yii::t('simialbi/ticket/model/topic', 'ticket update')]
            ),
            'on_ticket_assignment' => Yii::t(
                'simialbi/ticket/model/topic',
                'Notification on {event}',
                ['event' => Yii::t('simialbi/ticket/model/topic', 'ticket assignment')]
            ),
            'on_ticket_resolution' => Yii::t(
                'simialbi/ticket/model/topic',
                'Notification on {event}',
                ['event' => Yii::t('simialbi/ticket/model/topic', 'ticket resolution')]
            ),
            'on_ticket_comment' => Yii::t(
                'simialbi/ticket/model/topic',
                'Notification on {event}',
                ['event' => Yii::t('simialbi/ticket/model/topic', 'new comment on ticket')]
            ),
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
                ->from('{{%ticket__topic_agent}}')
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
