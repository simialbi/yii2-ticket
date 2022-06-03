<?php

namespace simialbi\yii2\ticket\migrations;

use simialbi\yii2\ticket\models\Topic;
use simialbi\yii2\ticket\models\TopicNotification;
use yii\db\Migration;

class m220602_164100_change_event_notifications extends Migration
{
    /** @var array All events on which the agents get notified */
    protected $events;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->events = Topic::getEvents();
    }

    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ticket__topic_notification}}', [
            'id' => $this->primaryKey()->unsigned(),
            'topic_id' => $this->integer()->unsigned()->notNull(),
            'event' => $this->string(64)->notNull(),
            'medium' => $this->string(64)->notNull()
        ]);

        $this->addForeignKey(
            '{{%ticket__topic_notification_ibfk_1}}',
            '{{%ticket__topic_notification}}',
            'topic_id',
            '{{%ticket__topic}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // insert existing notifications
        foreach (Topic::find()->all() as $topic) {
            foreach ($this->events as $event) {
                if ($topic->{$event} != null) {
                    $model = new TopicNotification([
                        'topic_id' => $topic->id,
                        'event' => $event,
                        'medium' => $topic->{$event}
                    ]);
                    $model->save();
                }
            }
        }

        // drop columns in topic
        foreach ($this->events as $event) {
            $this->dropColumn('{{%ticket__topic}}', $event);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $i = 0;
        foreach ($this->events as $key => $event) {
            $after = $i > 0 ? $this->events[array_keys($this->events)[$i-1]] : 'status';
            $this->addColumn('{{%ticket__topic}}', $event, $this->getColumnDefinition($after));
            $i++;
        }

        foreach (TopicNotification::find()->all() as $notification) {
            $topic = Topic::findOne($notification->topic_id);
            $topic->{$notification->event} = $notification->medium;
            $topic->save();
        }

        $this->dropForeignKey('{{%ticket__topic_notification_ibfk_1}}', '{{%ticket__topic_notification}}');
        $this->dropTable('{{%ticket__topic_notification}}');
    }

    /**
     * Generate column definition based on driver
     * @param string $after
     * @return string
     */
    protected function getColumnDefinition($after = '')
    {
        return 'ENUM(\'sms\', \'mail\') NULL DEFAULT NULL' . (!empty($after) ? " AFTER [[$after]]" : '');
    }
}
