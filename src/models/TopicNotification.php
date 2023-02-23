<?php

namespace simialbi\yii2\ticket\models;

use simialbi\yii2\ticket\Module;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%ticket__topic_notification}}".
 *
 * @property integer $topic_id
 * @property string $event
 * @property string $medium
 */
class TopicNotification extends ActiveRecord
{
    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '{{%ticket__topic_notification}}';
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            ['topic_id', 'integer'],
            [['event', 'medium'], 'string', 'max' => 64],
            [['topic_id', 'event', 'medium'], 'required'],
            [
                'topic_id',
                'exist',
                'targetClass' => Topic::class,
                'targetAttribute' => 'id'
            ],
            ['medium', 'in', 'range' => [Topic::BEHAVIOR_MAIL, Topic::BEHAVIOR_SMS]],
            ['event', 'in', 'range' => [
                Module::EVENT_TICKET_CREATED,
                Module::EVENT_TICKET_ASSIGNED,
                Module::EVENT_TICKET_UPDATED,
                Module::EVENT_TICKET_RESOLVED,
                Module::EVENT_TICKET_COMMENTED
            ]]
        ];
    }
}
