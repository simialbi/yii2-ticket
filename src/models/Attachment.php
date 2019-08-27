<?php

namespace simialbi\yii2\ticket\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%ticket_attachment}}".
 *
 * @property integer $id
 * @property integer $ticket_id
 * @property string $name
 * @property string $path
 * @property string $mime_type
 * @property integer $size
 * @property string $created_by
 * @property string $updated_by
 * @property integer|string $created_at
 * @property integer|string $updated_at
 *
 * @property-read Ticket $ticket
 */
class Attachment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '{{%ticket_attachment}}';
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [['ticket_id', 'size'], 'integer'],
            [['name', 'mime_type'], 'string', 'max' => 255],
            ['path', 'string', 'max' => 512],
            [
                ['ticket_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Ticket::class,
                'targetAttribute' => ['ticket_id' => 'id']
            ],

            [['ticket_id', 'name', 'path', 'mime_type', 'size'], 'required']
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
                    self::EVENT_BEFORE_UPDATE => 'updated_by'
                ]
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
            'id' => Yii::t('simialbi/ticket/model/attachment', 'ID'),
            'ticket_id' => Yii::t('simialbi/ticket/model/attachment', 'Ticket ID'),
            'name' => Yii::t('simialbi/ticket/model/attachment', 'Name'),
            'path' => Yii::t('simialbi/ticket/model/attachment', 'Path'),
            'mime_type' => Yii::t('simialbi/ticket/model/attachment', 'Mime Type'),
            'size' => Yii::t('simialbi/ticket/model/attachment', 'Size'),
            'created_by' => Yii::t('simialbi/ticket/model/attachment', 'Created By'),
            'updated_by' => Yii::t('simialbi/ticket/model/attachment', 'Updated By'),
            'created_at' => Yii::t('simialbi/ticket/model/attachment', 'Created At'),
            'updated_at' => Yii::t('simialbi/ticket/model/attachment', 'Updated At'),
        ];
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
