<?php

namespace simialbi\yii2\ticket\models;

use simialbi\yii2\models\UserInterface;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
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
 * @property string $created_by
 * @property string $updated_by
 * @property integer|string $created_at
 * @property integer|string $updated_at
 *
 * @property-read string[] $events
 * @property-read string[] $notificationBehaviors
 * @property-read UserInterface[] $agents
 * @property-read UserInterface $newTicketAgent
 * @property-read Ticket[] $tickets
 * @property-read TopicNotification[] $notifications
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
            'created_by' => Yii::t('simialbi/ticket/model/topic', 'Created By'),
            'updated_by' => Yii::t('simialbi/ticket/model/topic', 'Updated By'),
            'created_at' => Yii::t('simialbi/ticket/model/topic', 'Created At'),
            'updated_at' => Yii::t('simialbi/ticket/model/topic', 'Updated At'),
        ];
    }

    /**
     * Returns a list of all notification behaviors
     * @return string[]
     */
    public static function getNotificationBehaviors()
    {
        return [
            static::BEHAVIOR_SMS => Yii::t('simialbi/ticket', 'Send SMS'),
            static::BEHAVIOR_MAIL => Yii::t('simialbi/ticket', 'Send mail'),
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

    /**
     * Get associated notifications
     * @return ActiveQuery
     */
    public function getNotifications()
    {
        return $this->hasMany(TopicNotification::class, ['topic_id' => 'id']);
    }

    /**
     * Whether the topic should send a notification on the provided event
     * @param $event
     * @param $medium
     * @return boolean
     */
    public function hasNotification($event, $medium)
    {
        return TopicNotification::find()
                ->where([
                    'topic_id' => $this->id,
                    'event' => $event,
                    'medium' => $medium
                ])
                ->count() > 0;
    }
}
