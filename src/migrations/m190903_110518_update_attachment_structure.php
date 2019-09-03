<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\migrations;

use yii\db\Migration;
use yii\db\Query;

class m190903_110518_update_attachment_structure extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        $this->alterColumn(
            '{{%ticket_attachment}}',
            'ticket_id',
            $this->integer()->unsigned()->null()->defaultValue(null)
        );
        $this->addColumn(
            '{{%ticket_attachment}}',
            'comment_id',
            $this->integer()->unsigned()->null()->defaultValue(null)->after('ticket_id')
        );
        $this->addColumn(
            '{{%ticket_attachment}}',
            'unique_id',
            $this->string()->null()->defaultValue(null)->after('id')
        );
        $query = new Query();
        $query->select('*')->from('{{%ticket_attachment}}');
        foreach ($query->all() as $row) {
            $uniqueId = sprintf(
                '%s-%s',
                $row['size'],
                preg_replace('/[^0-9a-zA-Z_-]/i', '', $row['name'])
            );
            $this->update('{{%ticket_attachment}}', ['unique_id' => $uniqueId], ['id' => $row['id']]);
        }
        $this->alterColumn(
            '{{%ticket_attachment}}',
            'unique_id',
            $this->string()->notNull()->after('id')
        );
        $this->addForeignKey(
            '{{%ticket_attachment_ibfk_2}}',
            '{{%ticket_attachment}}',
            'comment_id',
            '{{%ticket_comment}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createIndex(
            '{{%ticket_attachment_unique_id}}',
            '{{%ticket_attachment}}',
            'unique_id',
            true
        );
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%ticket_attachment_ibfk_2}}', '{{%ticket_attachment}}');
        $this->dropIndex('{{%ticket_attachment_unique_id}}', '{{%ticket_attachment}}');

        $this->dropColumn('{{%ticket_attachment}}', 'unique_id');
        $this->dropColumn('{{%ticket_attachment}}', 'comment_id');

        $this->delete('{{%ticket_attachment}}', ['ticket_id' => null]);
        $this->alterColumn(
            '{{%ticket_attachment}}',
            'ticket_id',
            $this->integer()->unsigned()->notNull()
        );
    }
}
