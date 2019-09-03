<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\migrations;

use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\rbac\AssigneeRule;
use simialbi\yii2\ticket\rbac\AuthorRule;
use simialbi\yii2\ticket\rbac\TopicRule;
use Yii;
use yii\db\Migration;

class m190722_110712_init extends Migration
{
    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $this->createTable('{{%ticket_ticket}}', [
            'id' => $this->primaryKey()->unsigned(),
            'assigned_to' => $this->string(64)->null()->defaultValue(null),
            'source_id' => $this->integer()->unsigned()->notNull(),
            'topic_id' => $this->integer()->unsigned()->notNull(),
            'subject' => $this->string(255)->notNull(),
            'description' => $this->text()->notNull(),
            'due_date' => $this->integer()->unsigned()->null()->defaultValue(null),
            'status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(Ticket::STATUS_OPEN),
            'priority' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(2),
            'created_by' => $this->string(64)->null()->defaultValue(null),
            'updated_by' => $this->string(64)->null()->defaultValue(null),
            'assigned_by' => $this->string(64)->null()->defaultValue(null),
            'closed_by' => $this->string(64)->null()->defaultValue(null),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
            'assigned_at' => $this->integer()->unsigned()->null()->defaultValue(null),
            'closed_at' => $this->integer()->unsigned()->null()->defaultValue(null)
        ]);
        $this->createTable('{{%ticket_source}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'created_by' => $this->string(64)->null()->defaultValue(null),
            'updated_by' => $this->string(64)->null()->defaultValue(null),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull()
        ]);
        $this->createTable('{{%ticket_topic}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'new_ticket_assign_to' => $this->string(64)->null()->defaultValue(null),
            'new_ticket_status' => $this->tinyInteger()->unsigned()->notNull()->defaultValue(Ticket::STATUS_OPEN),
            'status' => $this->boolean()->notNull()->defaultValue(1),
            'created_by' => $this->string(64)->null()->defaultValue(null),
            'updated_by' => $this->string(64)->null()->defaultValue(null),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull()
        ]);
        $this->createTable('{{%ticket_attachment}}', [
            'id' => $this->primaryKey()->unsigned(),
            'ticket_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string(255)->notNull(),
            'path' => $this->string(512)->notNull(),
            'mime_type' => $this->string(255)->notNull(),
            'size' => $this->integer()->unsigned()->notNull(),
            'created_by' => $this->string(64)->null()->defaultValue(null),
            'updated_by' => $this->string(64)->null()->defaultValue(null),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull()
        ]);
        $this->createTable('{{%ticket_comment}}', [
            'id' => $this->primaryKey()->unsigned()->notNull(),
            'ticket_id' => $this->integer()->unsigned()->notNull(),
            'text' => $this->text()->notNull(),
            'created_by' => $this->string(64)->null()->defaultValue(null),
            'created_at' => $this->integer()->unsigned()->notNull()
        ]);
        $this->createTable('{{%ticket_topic_agent}}', [
            'topic_id' => $this->integer()->unsigned()->notNull(),
            'agent_id' => $this->string(64)->null()->defaultValue(null),
            'PRIMARY KEY ([[topic_id]], [[agent_id]])'
        ]);

        $this->addForeignKey(
            '{{%ticket_ticket_ibfk_1}}',
            '{{%ticket_ticket}}',
            'source_id',
            '{{%ticket_source}}',
            'id',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%ticket_ticket_ibfk_2}}',
            '{{%ticket_ticket}}',
            'topic_id',
            '{{%ticket_topic}}',
            'id',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%ticket_attachment_ibfk_1}}',
            '{{%ticket_attachment}}',
            'ticket_id',
            '{{%ticket_ticket}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%ticket_comment_ibfk_1}}',
            '{{%ticket_comment}}',
            'ticket_id',
            '{{%ticket_ticket}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%ticket_topic_agent_ibfk_1}}',
            '{{%ticket_topic_agent}}',
            'topic_id',
            '{{%ticket_topic}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $now = Yii::$app->formatter->asTimestamp('now');
        $this->batchInsert('{{%ticket_source}}', ['name', 'created_at', 'updated_at'], [
            [Yii::t('simialbi/ticket/source', 'Ticket'), $now, $now],
            [Yii::t('simialbi/ticket/source', 'Phone'), $now, $now],
            [Yii::t('simialbi/ticket/source', 'Email'), $now, $now],
            [Yii::t('simialbi/ticket/source', 'Other'), $now, $now]
        ]);

        if ($auth) {
            $createTicket = $auth->createPermission('createTicket');
            $createTicket->description = 'Create a ticket';
            $auth->add($createTicket);

            $viewTicket = $auth->createPermission('viewTicket');
            $viewTicket->description = 'View ticket status and conversation';
            $auth->add($viewTicket);

            $updateTicket = $auth->createPermission('updateTicket');
            $updateTicket->description = 'Update a ticket (add comments, add attachments)';
            $auth->add($updateTicket);

            $takeTicket = $auth->createPermission('takeTicket');
            $takeTicket->description = 'Take a ticket';
            $auth->add($takeTicket);

            $closeTicket = $auth->createPermission('closeTicket');
            $closeTicket->description = 'Close a ticket';
            $auth->add($closeTicket);

            $assignTicket = $auth->createPermission('assignTicket');
            $assignTicket->description = 'Assign a ticket to an agent';
            $auth->add($assignTicket);

            $changeSettings = $auth->createPermission('changeTicketSettings');
            $changeSettings->description = 'Change administrative settings like sources or topics';
            $auth->add($changeSettings);

            $author = $auth->createRole('ticketAuthor');
            $auth->add($author);
            $auth->addChild($author, $createTicket);

            $agent = $auth->createRole('ticketAgent');
            $agent->description = 'Ticket agent (handle tickets)';
            $auth->add($agent);
            $auth->addChild($agent, $createTicket);
            $auth->addChild($agent, $viewTicket);

            $administrator = $auth->createRole('ticketAdministrator');
            $administrator->description = 'Ticket administrator (an agent with more permissions)';
            $auth->add($administrator);

            $auth->addChild($administrator, $takeTicket);
            $auth->addChild($administrator, $assignTicket);
            $auth->addChild($administrator, $changeSettings);
            $auth->addChild($administrator, $agent);

            $rule = new TopicRule();
            $auth->add($rule);

            $takeTopicTicket = $auth->createPermission('takeTopicTicket');
            $takeTopicTicket->description = 'Take a ticket from a topic an agent is responsible for';
            $takeTopicTicket->ruleName = $rule->name;
            $auth->add($takeTopicTicket);

            $auth->addChild($agent, $takeTopicTicket);

            $rule = new AssigneeRule();
            $auth->add($rule);

            $administrateTicket = $auth->createPermission('administrateTicket');
            $administrateTicket->description = 'Administrate ticket (add agent comments, change status)';
            $administrateTicket->ruleName = $rule->name;
            $auth->add($administrateTicket);

            $auth->addChild($administrateTicket, $updateTicket);
            $auth->addChild($administrateTicket, $closeTicket);
            $auth->addChild($agent, $administrateTicket);

            $rule = new AuthorRule();
            $auth->add($rule);

            $viewOwnTicket = $auth->createPermission('viewOwnTicket');
            $viewOwnTicket->description = 'View own ticket';
            $viewOwnTicket->ruleName = $rule->name;
            $auth->add($viewOwnTicket);

            $updateOwnTicket = $auth->createPermission('updateOwnTicket');
            $updateOwnTicket->description = 'Update own ticket';
            $updateOwnTicket->ruleName = $rule->name;
            $auth->add($updateOwnTicket);

            $closeOwnTicket = $auth->createPermission('closeOwnTicket');
            $closeOwnTicket->description = 'Close own ticket';
            $closeOwnTicket->ruleName = $rule->name;
            $auth->add($closeOwnTicket);

            $auth->addChild($viewOwnTicket, $viewTicket);
            $auth->addChild($author, $viewOwnTicket);
            $auth->addChild($updateOwnTicket, $updateTicket);
            $auth->addChild($author, $updateOwnTicket);
            $auth->addChild($closeOwnTicket, $closeTicket);
            $auth->addChild($author, $closeOwnTicket);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $this->dropForeignKey('{{%ticket_topic_agent_ibfk_1}}', '{{%ticket_topic_agent}}');
        $this->dropForeignKey('{{%ticket_ticket_ibfk_1}}', '{{%ticket_ticket}}');
        $this->dropForeignKey('{{%ticket_ticket_ibfk_2}}', '{{%ticket_ticket}}');
        $this->dropForeignKey('{{%ticket_attachment_ibfk_1}}', '{{%ticket_attachment}}');
        $this->dropForeignKey('{{%ticket_comment_ibfk_1}}', '{{%ticket_comment}}');

        $this->dropTable('{{%ticket_topic_agent}}');
        $this->dropTable('{{%ticket_comment}}');
        $this->dropTable('{{%ticket_attachment}}');
        $this->dropTable('{{%ticket_topic}}');
        $this->dropTable('{{%ticket_source}}');
        $this->dropTable('{{%ticket_ticket}}');

        if ($auth) {
            $viewOwnTicket = $auth->getPermission('viewOwnTicket');
            $updateOwnTicket = $auth->getPermission('updateOwnTicket');
            $closeOwnTicket = $auth->getPermission('closeOwnTicket');
            $takeTopicTicket = $auth->getPermission('takeTopicTicket');
            $takeTicket = $auth->getPermission('takeTicket');
            $updateTicket = $auth->getPermission('updateTicket');
            $closeTicket = $auth->getPermission('closeTicket');
            $viewTicket = $auth->getPermission('viewTicket');
            $createTicket = $auth->getPermission('createTicket');
            $administrateTicket = $auth->getPermission('administrateTicket');
            $changeSettings = $auth->getPermission('changeTicketSettings');
            $assignTicket = $auth->getPermission('assignTicket');
            $agent = $auth->getRole('ticketAgent');
            $author = $auth->getRole('ticketAuthor');
            $administrator = $auth->getRole('ticketAdministrator');

            $auth->removeChildren($viewOwnTicket);
            $auth->removeChildren($updateOwnTicket);
            $auth->removeChildren($closeOwnTicket);
            $auth->removeChildren($takeTopicTicket);
            $auth->removeChildren($administrateTicket);
            $auth->removeChildren($agent);
            $auth->removeChildren($author);
            $auth->removeChildren($administrator);
            $auth->remove($viewOwnTicket);
            $auth->remove($updateOwnTicket);
            $auth->remove($closeOwnTicket);
            $auth->remove($takeTopicTicket);
            $auth->remove($takeTicket);
            $auth->remove($updateTicket);
            $auth->remove($closeTicket);
            $auth->remove($viewTicket);
            $auth->remove($createTicket);
            $auth->remove($administrateTicket);
            $auth->remove($changeSettings);
            $auth->remove($assignTicket);
            $auth->remove($agent);
            $auth->remove($author);
            $auth->remove($administrator);
        }
    }
}
