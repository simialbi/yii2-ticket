<?php

namespace simialbi\yii2\ticket\migrations;

class m220201_150934_rename_tables extends \yii\db\Migration
{
    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        $this->renameTable('{{%ticket_attachment}}', '{{%ticket__attachment}}');
        $this->renameTable('{{%ticket_attachment_comment}}', '{{%ticket__attachment_comment}}');
        $this->renameTable('{{%ticket_attachment_ticket}}', '{{%ticket__attachment_ticket}}');
        $this->renameTable('{{%ticket_comment}}', '{{%ticket__comment}}');
        $this->renameTable('{{%ticket_source}}', '{{%ticket__source}}');
        $this->renameTable('{{%ticket_ticket}}', '{{%ticket__ticket}}');
        $this->renameTable('{{%ticket_topic}}', '{{%ticket__topic}}');
        $this->renameTable('{{%ticket_topic_agent}}', '{{%ticket__topic_agent}}');
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $this->renameTable('{{%ticket__attachment}}', '{{%ticket_attachment}}');
        $this->renameTable('{{%ticket__attachment_comment}}', '{{%ticket_attachment_comment}}');
        $this->renameTable('{{%ticket__attachment_ticket}}', '{{%ticket_attachment_ticket}}');
        $this->renameTable('{{%ticket__comment}}', '{{%ticket_comment}}');
        $this->renameTable('{{%ticket__source}}', '{{%ticket_source}}');
        $this->renameTable('{{%ticket__ticket}}', '{{%ticket_ticket}}');
        $this->renameTable('{{%ticket__topic}}', '{{%ticket_topic}}');
        $this->renameTable('{{%ticket__topic_agent}}', '{{%ticket_topic_agent}}');
    }
}
