<?php

namespace simialbi\yii2\ticket\migrations;

use simialbi\yii2\ticket\Module;
use yii\db\Migration;

/**
 *
 */
class m230217_103142_remove_topic_event_constants extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        $this->update('{{%ticket__topic_notification}}', [
            'event' => Module::EVENT_TICKET_CREATED
        ], [
            'event' => 'on_new_ticket'
        ]);
        $this->update('{{%ticket__topic_notification}}', [
            'event' => Module::EVENT_TICKET_UPDATED
        ], [
            'event' => 'on_ticket_update'
        ]);
        $this->update('{{%ticket__topic_notification}}', [
            'event' => Module::EVENT_TICKET_ASSIGNED
        ], [
            'event' => 'on_ticket_assignment'
        ]);
        $this->update('{{%ticket__topic_notification}}', [
            'event' => Module::EVENT_TICKET_RESOLVED
        ], [
            'event' => 'on_ticket_resolution'
        ]);
        $this->update('{{%ticket__topic_notification}}', [
            'event' => Module::EVENT_TICKET_COMMENTED
        ], [
            'event' => 'on_ticket_comment'
        ]);
    }

    public function safeDown()
    {
        $this->update('{{%ticket__topic_notification}}', [
            'event' => 'on_new_ticket'
        ], [
            'event' => Module::EVENT_TICKET_CREATED
        ]);
        $this->update('{{%ticket__topic_notification}}', [
            'event' => 'on_ticket_update'
        ], [
            'event' => Module::EVENT_TICKET_UPDATED
        ]);
        $this->update('{{%ticket__topic_notification}}', [
            'event' => 'on_ticket_assignment'
        ], [
            'event' => Module::EVENT_TICKET_ASSIGNED
        ]);
        $this->update('{{%ticket__topic_notification}}', [
            'event' => 'on_ticket_resolution'
        ], [
            'event' => Module::EVENT_TICKET_RESOLVED
        ]);
        $this->update('{{%ticket__topic_notification}}', [
            'event' => 'on_ticket_comment'
        ], [
            'event' => Module::EVENT_TICKET_COMMENTED
        ]);
    }
}
