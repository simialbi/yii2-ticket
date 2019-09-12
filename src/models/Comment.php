<?php

namespace simialbi\yii2\ticket\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%ticket_comment}}".
 *
 * @property integer $id
 * @property integer $ticket_id
 * @property string $text
 * @property string $created_by
 * @property integer|string $created_at
 *
 * @property-read \simialbi\yii2\models\UserInterface $author
 * @property-read Attachment[] $attachments
 * @property-read Ticket $ticket
 */
class Comment extends ActiveRecord
{
    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '{{%ticket_comment}}';
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            ['ticket_id', 'integer'],
            ['text', 'string'],
            [
                ['ticket_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Ticket::class,
                'targetAttribute' => ['ticket_id' => 'id']
            ],

            [['ticket_id', 'text'], 'required']
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
                    self::EVENT_BEFORE_INSERT => 'created_by'
                ]
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'created_at'
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
            'id' => Yii::t('simialbi/ticket/model/comment', 'ID'),
            'ticket_id' => Yii::t('simialbi/ticket/model/comment', 'Ticket ID'),
            'text' => Yii::t('simialbi/ticket/model/comment', 'Text'),
            'created_by' => Yii::t('simialbi/ticket/model/comment', 'Created By'),
            'created_at' => Yii::t('simialbi/ticket/model/comment', 'Created At'),
        ];
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
     * Get associated attachments
     * @return \yii\db\ActiveQuery
     */
    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['comment_id' => 'id'])->orderBy([
            'created_at' => SORT_ASC,
            'name' => SORT_ASC
        ]);
    }

    /**
     * Get associated tickets
     * @return \yii\db\ActiveQuery
     */
    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['id' => 'ticket_id']);
    }
}
