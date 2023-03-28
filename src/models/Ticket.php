<?php

namespace simialbi\yii2\ticket\models;

use simialbi\yii2\kanban\models\Task;
use Yii;
use yii\base\ModelEvent;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%ticket__ticket}}".
 *
 * @property integer $id
 * @property string $assigned_to
 * @property integer $source_id
 * @property integer $topic_id
 * @property string $subject
 * @property string $description
 * @property integer $due_date
 * @property string $assignment_comment
 * @property integer $status
 * @property integer $priority
 * @property string $created_by
 * @property string $updated_by
 * @property string $assigned_by
 * @property string $closed_by
 * @property integer|string $created_at
 * @property integer|string $updated_at
 * @property integer|string $assigned_at
 * @property integer|string $closed_at
 *
 * @property-read \simialbi\yii2\models\UserInterface $author
 * @property-read \simialbi\yii2\models\UserInterface $agent
 * @property-read \simialbi\yii2\models\UserInterface $referrer
 * @property-read \simialbi\yii2\models\UserInterface $closer
 * @property-read \simialbi\yii2\models\UserInterface $updater
 * @property-read array $history
 * @property-read Comment $solution
 * @property-read Attachment[] $attachments
 * @property-read Comment[] $comments
 * @property-read Source $source
 * @property-read Topic $topic
 * @property-read \simialbi\yii2\kanban\models\Task $task
 */
class Ticket extends ActiveRecord
{
    const EVENT_BEFORE_CLOSE = 'beforeClose';
    const EVENT_AFTER_CLOSE = 'afterClose';
    const EVENT_BEFORE_ASSIGN = 'beforeAssign';
    const EVENT_AFTER_ASSIGN = 'afterAssign';
    const EVENT_AFTER_ADD_COMMENT = 'afterAddComment';

    const SCENARIO_ASSIGN = 'assign';
    const SCENARIO_COMMENT = 'comment';

    const STATUS_RESOLVED = 0;
    const STATUS_IN_PROGRESS = 3;
    const STATUS_ASSIGNED = 5;
    const STATUS_OPEN = 10;
    const STATUS_LATE = 15;

    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_EMERGENCY = 4;

    /**
     * @var array Ticket history
     */
    private $_history;

    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '{{%ticket__ticket}}';
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [['source_id', 'topic_id', 'status', 'priority'], 'integer'],
            [['description', 'assignment_comment'], 'string'],
            ['assigned_to', 'string', 'max' => 64],
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
                'exist',
                'skipOnError' => true,
                'targetClass' => Topic::class,
                'targetAttribute' => ['topic_id' => 'id']
            ],
            [
                'priority',
                'in',
                'range' => [self::PRIORITY_LOW, self::PRIORITY_NORMAL, self::PRIORITY_HIGH, self::PRIORITY_EMERGENCY]
            ],
            [
                'status',
                'in',
                'range' => [
                    self::STATUS_LATE,
                    self::STATUS_OPEN,
                    self::STATUS_ASSIGNED,
                    self::STATUS_IN_PROGRESS,
                    self::STATUS_RESOLVED
                ]
            ],
            ['due_date', 'date', 'format' => 'dd.MM.yyyy', 'timestampAttribute' => 'due_date'],

            [['assigned_to', 'due_date', 'assignment_comment'], 'default'],
            ['status', 'default', 'value' => $this->topic ? $this->topic->new_ticket_status : self::STATUS_OPEN],
            ['priority', 'default', 'value' => self::PRIORITY_NORMAL],
            ['status', 'default', 'value' => self::STATUS_IN_PROGRESS, 'on' => self::SCENARIO_COMMENT],

            ['created_by', 'safe'],
            ['assigned_to', 'safe'],

            [['source_id', 'topic_id', 'subject', 'description'], 'required'],
            ['assigned_to', 'required', 'on' => self::SCENARIO_ASSIGN],
            ['status', 'required', 'on' => self::SCENARIO_COMMENT]
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
                    self::EVENT_BEFORE_INSERT => ['created_by', 'updated_by']
                ],
                'preserveNonEmptyValues' => true
            ],
            'blameable2' => [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_UPDATE => 'updated_by',
                    self::EVENT_BEFORE_ASSIGN => 'assigned_by',
                    self::EVENT_BEFORE_CLOSE => 'closed_by'
                ],
                'preserveNonEmptyValues' => false
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'status' => AttributeTypecastBehavior::TYPE_INTEGER
                ],
                'typecastAfterValidate' => false,
                'typecastBeforeSave' => true,
                'typecastAfterFind' => true
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    self::EVENT_BEFORE_UPDATE => 'updated_at',
                    self::EVENT_BEFORE_ASSIGN => 'assigned_at',
                    self::EVENT_BEFORE_CLOSE => 'closed_at'
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
            'id' => Yii::t('simialbi/ticket/model/ticket', 'ID'),
            'assigned_to' => Yii::t('simialbi/ticket/model/ticket', 'Assigned To'),
            'source_id' => Yii::t('simialbi/ticket/model/ticket', 'Source ID'),
            'topic_id' => Yii::t('simialbi/ticket/model/ticket', 'Topic ID'),
            'subject' => Yii::t('simialbi/ticket/model/ticket', 'Subject'),
            'description' => Yii::t('simialbi/ticket/model/ticket', 'Description'),
            'due_date' => Yii::t('simialbi/ticket/model/ticket', 'Due Date'),
            'assignment_comment' => Yii::t('simialbi/ticket/model/ticket', 'Assignment comment'),
            'status' => Yii::t('simialbi/ticket/model/ticket', 'Status'),
            'priority' => Yii::t('simialbi/ticket/model/ticket', 'Priority'),
            'created_by' => Yii::t('simialbi/ticket/model/ticket', 'Created By'),
            'updated_by' => Yii::t('simialbi/ticket/model/ticket', 'Updated By'),
            'closed_by' => Yii::t('simialbi/ticket/model/ticket', 'Closed By'),
            'created_at' => Yii::t('simialbi/ticket/model/ticket', 'Created At'),
            'updated_at' => Yii::t('simialbi/ticket/model/ticket', 'Updated At'),
            'assigned_at' => Yii::t('simialbi/ticket/model/comment', 'Assigned At'),
            'closed_at' => Yii::t('simialbi/ticket/model/ticket', 'Closed At')
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave($insert)
    {
        if ($this->isAttributeChanged('status') && (int)$this->status === self::STATUS_RESOLVED) {
            if (!$this->beforeClose()) {
                return false;
            }
        }
        if ($this->isAttributeChanged('assigned_to')) {
            if (!$this->beforeAssign()) {
                return false;
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['status']) && (int)$this->status === self::STATUS_RESOLVED) {
            $this->afterClose($changedAttributes);
        }
        // Do not change
        if (array_key_exists($changedAttributes['assigned_to']) && !empty($this->assigned_to)) {
            $this->afterAssign($changedAttributes);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /** This method is called before a ticket gets assigned.
     *
     * The default implementation will trigger an [[EVENT_BEFORE_ASSIGN]] event.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeAssign()
     * {
     *     if (!parent::beforeAssign()) {
     *         return false;
     *     }
     *
     *     // ...custom code here...
     *     return true;
     * }
     * ```
     *
     * @return bool whether the insertion or updating should continue.
     * If `false`, the insertion or updating will be cancelled.
     */
    public function beforeAssign()
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_ASSIGN, $event);

        return $event->isValid;
    }

    /**
     * This method is called after assigning a ticket.
     * The default implementation will trigger an [[EVENT_AFTER_ASSIGN]] event.
     * The event class used is [[AfterSaveEvent]]. When overriding this method, make sure you call the
     * parent implementation so that the event is triggered.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     * You can use this parameter to take action based on the changes made for example send an email
     * when the password had changed or implement audit trail that tracks all the changes.
     * `$changedAttributes` gives you the old attribute values while the active record (`$this`) has
     * already the new, updated values.
     *
     * Note that no automatic type conversion performed by default. You may use
     * [[\yii\behaviors\AttributeTypecastBehavior]] to facilitate attribute typecasting.
     * See http://www.yiiframework.com/doc-2.0/guide-db-active-record.html#attributes-typecasting.
     */
    public function afterAssign($changedAttributes)
    {
        $this->trigger(self::EVENT_AFTER_ASSIGN, new AfterSaveEvent([
            'changedAttributes' => $changedAttributes,
        ]));
    }

    /**
     * This method is called before a ticket will be closed.
     *
     * The default implementation will trigger an [[EVENT_BEFORE_CLOSE]] event.
     * When overriding this method, make sure you call the parent implementation like the following:
     *
     * ```php
     * public function beforeClose()
     * {
     *     if (!parent::beforeClose()) {
     *         return false;
     *     }
     *
     *     // ...custom code here...
     *     return true;
     * }
     * ```
     *
     * @return bool whether the insertion or updating should continue.
     * If `false`, the insertion or updating will be cancelled.
     */
    public function beforeClose()
    {
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_CLOSE, $event);

        return $event->isValid;
    }

    /**
     * This method is called after closing a ticket.
     * The default implementation will trigger an [[EVENT_AFTER_CLOSE]] event.
     * The event class used is [[AfterSaveEvent]]. When overriding this method, make sure you call the
     * parent implementation so that the event is triggered.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     * You can use this parameter to take action based on the changes made for example send an email
     * when the password had changed or implement audit trail that tracks all the changes.
     * `$changedAttributes` gives you the old attribute values while the active record (`$this`) has
     * already the new, updated values.
     *
     * Note that no automatic type conversion performed by default. You may use
     * [[\yii\behaviors\AttributeTypecastBehavior]] to facilitate attribute typecasting.
     * See http://www.yiiframework.com/doc-2.0/guide-db-active-record.html#attributes-typecasting.
     */
    public function afterClose($changedAttributes)
    {
        $this->trigger(self::EVENT_AFTER_CLOSE, new AfterSaveEvent([
            'changedAttributes' => $changedAttributes,
        ]));
    }

    /**
     * Get author
     * @return \simialbi\yii2\models\UserInterface
     */
    public function getAuthor()
    {
        return call_user_func([Yii::$app->user->identityClass, 'findIdentity'], $this->created_by);
    }

    /**
     * Get responsible agent
     * @return \simialbi\yii2\models\UserInterface
     */
    public function getAgent()
    {
        return call_user_func([Yii::$app->user->identityClass, 'findIdentity'], $this->assigned_to);
    }

    /**
     * Get responsible referrer
     * @return \simialbi\yii2\models\UserInterface
     */
    public function getReferrer()
    {
        return call_user_func([Yii::$app->user->identityClass, 'findIdentity'], $this->assigned_by);
    }

    /**
     * Get user who closed the ticket
     * @return \simialbi\yii2\models\UserInterface
     */
    public function getCloser()
    {
        return call_user_func([Yii::$app->user->identityClass, 'findIdentity'], $this->closed_by);
    }

    /**
     * Get user who updated the ticket
     * @return \simialbi\yii2\models\UserInterface
     */
    public function getUpdater()
    {
        return call_user_func([Yii::$app->user->identityClass, 'findIdentity'], $this->updated_by);
    }

    /**
     * Gets the ticket history
     *
     * @return array
     */
    public function getHistory()
    {
        if (empty($this->_history)) {
            $this->_history = array_map(function ($item) {
                return [$item];
            }, $this->getComments()->indexBy('created_at')->all());
            $this->_history[$this->created_at][] = Yii::t(
                'simialbi/ticket/history',
                '{user} created the ticket',
                [
                    'user' => $this->author->name
                ]
            );
            if (isset($this->assigned_at) && isset($this->assigned_to)) {
                if ($this->assigned_to === $this->assigned_by) {
                    $this->_history[$this->assigned_at][] = Yii::t(
                        'simialbi/ticket/history',
                        '{agent} took ticket at {assigned_at,date} {assigned_at,time}',
                        [
                            'agent' => $this->agent->name,
                            'assigned_at' => $this->assigned_at
                        ]
                    );
                } else {
                    $msg = Yii::t(
                        'simialbi/ticket/history',
                        '{referrer} assigned {agent} to the ticket at {assigned_at,date} {assigned_at,time}',
                        [
                            'referrer' => $this->referrer->name,
                            'agent' => $this->agent->name,
                            'assigned_at' => $this->assigned_at
                        ]
                    );
                    if ($this->assignment_comment && $this->assigned_to == Yii::$app->user->id) {
                        $msg .= Html::tag('br') . "\n";
                        $msg .= Html::tag('em', $this->assignment_comment, ['class' => 'small']);
                    }

                    $this->_history[$this->assigned_at][] = $msg;
                }
            }
            if ($this->closed_at && $this->status === self::STATUS_RESOLVED) {
                $this->_history[$this->closed_at][] = Yii::t(
                    'simialbi/ticket/history',
                    '{agent} closed ticket at {closed_at,date} {closed_at,time}',
                    [
                        'agent' => $this->closer->name,
                        'closed_at' => $this->closed_at
                    ]
                );
            }

            krsort($this->_history);
        }

        return $this->_history;
    }

    /**
     * Get solution
     * @return Comment|null
     */
    public function getSolution()
    {
        if ($this->status !== self::STATUS_RESOLVED) {
            return null;
        }

        $solution = null;
        foreach ($this->comments as $comment) {
            if ($comment->created_by === $this->assigned_to) {
                $solution = $comment;
                break;
            }
        }

        return $solution;
    }

    /**
     * Get associated attachments
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['id' => 'attachment_id'])
            ->viaTable('{{%ticket__attachment_ticket}}', ['ticket_id' => 'id'])
            ->union(
                $this->getComments()
                    ->orderBy(null)
                    ->innerJoinWith('attachments a2')
                    ->select('{{a2}}.*')
            );
    }

    /**
     * Get associated comments
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['ticket_id' => 'id'])->orderBy([
            'created_at' => SORT_DESC
        ]);
    }

    /**
     * Get associated source
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Source::class, ['id' => 'source_id']);
    }

    /**
     * Get associated topic
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(Topic::class, ['id' => 'topic_id']);
    }

    /**
     * Get associated task
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['ticket_id' => 'id']);
    }
}
