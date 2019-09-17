<?php

namespace simialbi\yii2\ticket\models;

use Yii;
use yii\base\ModelEvent;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

/**
 * This is the model class for table "{{%ticket_ticket}}".
 *
 * @property integer $id
 * @property string $assigned_to
 * @property integer $source_id
 * @property integer $topic_id
 * @property string $subject
 * @property string $description
 * @property integer $due_date
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
 * @property-read Comment $solution
 * @property-read Attachment[] $attachments
 * @property-read Comment[] $comments
 * @property-read Source $source
 * @property-read Topic $topic
 */
class Ticket extends ActiveRecord
{
    const EVENT_BEFORE_CLOSE = 'beforeClose';
    const EVENT_AFTER_CLOSE = 'afterClose';
    const EVENT_BEFORE_ASSIGN = 'beforeAssign';
    const EVENT_AFTER_ASSIGN = 'afterAssign';

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
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '{{%ticket_ticket}}';
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [['source_id', 'topic_id', 'due_date', 'status', 'priority'], 'integer'],
            ['description', 'string'],
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

            [['assigned_to', 'due_date'], 'default'],
            ['status', 'default', 'value' => $this->topic ? $this->topic->new_ticket_status : self::STATUS_OPEN],
            ['priority', 'default', 'value' => self::PRIORITY_NORMAL],
            ['status', 'default', 'value' => self::STATUS_IN_PROGRESS, 'on' => self::SCENARIO_COMMENT],

            ['created_by', 'safe'],

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
            'status' => Yii::t('simialbi/ticket/model/ticket', 'Status'),
            'priority' => Yii::t('simialbi/ticket/model/ticket', 'Priority'),
            'created_by' => Yii::t('simialbi/ticket/model/ticket', 'Created By'),
            'updated_by' => Yii::t('simialbi/ticket/model/ticket', 'Updated By'),
            'closed_by' => Yii::t('simialbi/ticket/model/ticket', 'Closed By'),
            'created_at' => Yii::t('simialbi/ticket/model/ticket', 'Created At'),
            'updated_at' => Yii::t('simialbi/ticket/model/ticket', 'Updated At'),
            'closed_at' => Yii::t('simialbi/ticket/model/ticket', 'Closed At')
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeSave($insert)
    {
        if ($this->isAttributeChanged('status') && $this->status === self::STATUS_RESOLVED) {
            $event = new ModelEvent();
            $this->trigger(self::EVENT_BEFORE_CLOSE, $event);

            if (!$event->isValid) {
                return false;
            }
        }
        if ($this->isAttributeChanged('assigned_to')) {
            $event = new ModelEvent();
            $this->trigger(self::EVENT_BEFORE_ASSIGN, $event);

            if (!$event->isValid) {
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
        if (isset($changedAttributes['status']) && $changedAttributes['status'] === self::STATUS_RESOLVED) {
            $this->trigger(self::EVENT_AFTER_CLOSE, new AfterSaveEvent([
                'changedAttributes' => $changedAttributes,
            ]));
        }
        if (isset($changedAttributes['assigned_to'])) {
            $this->trigger(self::EVENT_AFTER_ASSIGN, new AfterSaveEvent([
                'changedAttributes' => $changedAttributes,
            ]));
        }
        parent::afterSave($insert, $changedAttributes);
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
            ->viaTable('{{%ticket_attachment_ticket}}', ['ticket_id' => 'id'])
            ->union(
                $this->getComments()
                    ->innerJoinWith('attachments a2')
                    ->select('{{a2}}.*')
                    ->orderBy(null)
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
}
