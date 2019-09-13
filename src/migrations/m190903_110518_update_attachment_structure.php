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
        $query = new Query();
        $query->select('*')->from('{{%ticket_attachment}}');
        $rows = $query->all();

        $this->dropForeignKey('{{%ticket_attachment_ibfk_1}}', '{{%ticket_attachment}}');
        $this->dropColumn(
            '{{%ticket_attachment}}',
            'ticket_id'
        );
        $this->addColumn(
            '{{%ticket_attachment}}',
            'unique_id',
            $this->string()->null()->defaultValue(null)->after('id')
        );
        $this->createTable('{{%ticket_attachment_ticket}}', [
            'attachment_id' => $this->integer()->unsigned()->notNull(),
            'ticket_id' => $this->integer()->unsigned()->notNull(),
            'PRIMARY KEY ([[attachment_id]], [[ticket_id]])'
        ]);
        $this->createTable('{{%ticket_attachment_comment}}', [
            'attachment_id' => $this->integer()->unsigned()->notNull(),
            'comment_id' => $this->integer()->unsigned()->notNull(),
            'PRIMARY KEY ([[attachment_id]], [[comment_id]])'
        ]);
        $this->addForeignKey(
            '{{%ticket_attachment_ticket_ibfk_1}}',
            '{{%ticket_attachment_ticket}}',
            'attachment_id',
            '{{%ticket_attachment}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%ticket_attachment_ticket_ibfk_2}}',
            '{{%ticket_attachment_ticket}}',
            'ticket_id',
            '{{%ticket_ticket}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%ticket_attachment_comment_ibfk_1}}',
            '{{%ticket_attachment_comment}}',
            'attachment_id',
            '{{%ticket_attachment}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            '{{%ticket_attachment_comment_ibfk_2}}',
            '{{%ticket_attachment_comment}}',
            'comment_id',
            '{{%ticket_comment}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        foreach ($rows as $row) {
            $uniqueId = sprintf(
                '%s-%s',
                $row['size'],
                preg_replace('/[^0-9a-zA-Z_-]/i', '', $row['name'])
            );
            $this->update('{{%ticket_attachment}}', ['unique_id' => $uniqueId], ['id' => $row['id']]);
            if ($row['ticket_id']) {
                $this->insert('{{%ticket_attachment_ticket}}', [
                    'attachment_id' => $row['id'],
                    'ticket_id' => $row['ticket_id']
                ]);
            }
        }


        $this->alterColumn(
            '{{%ticket_attachment}}',
            'unique_id',
            $this->string()->notNull()->after('id')
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
        if ($this->isMSSQL()) {
            $tableName = $this->db->quoteTableName('{{%ticket_attachment}}');

            $sql = <<<SQL
DECLARE @sql nvarchar(max)

SET @sql = ''

SELECT @sql = 'ALTER TABLE $tableName DROP CONSTRAINT ' + [name]  + ';'
FROM [sys].[default_constraints]
WHERE [parent_object_id] = OBJECT_ID('$tableName')
AND [parent_column_id] = COLUMNPROPERTY(OBJECT_ID('$tableName'), 'unique_id', 'ColumnId')
AND [type] = 'D'

EXECUTE sp_executesql @sql
SQL;

            $this->execute($sql);
        }

        $this->dropForeignKey('{{%ticket_attachment_comment_ibfk_2}}', '{{%ticket_attachment_comment}}');
        $this->dropForeignKey('{{%ticket_attachment_comment_ibfk_1}}', '{{%ticket_attachment_comment}}');
        $this->dropForeignKey('{{%ticket_attachment_ticket_ibfk_2}}', '{{%ticket_attachment_ticket}}');
        $this->dropForeignKey('{{%ticket_attachment_ticket_ibfk_1}}', '{{%ticket_attachment_ticket}}');
        $this->dropIndex('{{%ticket_attachment_unique_id}}', '{{%ticket_attachment}}');

        $this->dropColumn('{{%ticket_attachment}}', 'unique_id');

        $this->addColumn(
            '{{%ticket_attachment}}',
            'ticket_id',
            $this->integer()->unsigned()->null()->defaultValue(null)->after('id')
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

        $query = new Query();
        $query->select('*')->from('{{%ticket_attachment_ticket}}');
        foreach ($query->all() as $row) {
            $this->update('{{%ticket_attachment}}', [
                'ticket_id' => $row['ticket_id']
            ], [
                'id' => $row['attachment_id']
            ]);
        }

        $this->delete('{{%ticket_attachment}}', ['ticket_id' => null]);
        $this->alterColumn(
            '{{%ticket_attachment}}',
            'ticket_id',
            $this->integer()->unsigned()->notNull()
        );

        if ($this->isMSSQL()) {
            $tableName = $this->db->quoteTableName('{{%ticket_attachment}}');

            $sql = <<<SQL
DECLARE @sql nvarchar(max)

SET @sql = ''

SELECT @sql = 'ALTER TABLE $tableName DROP CONSTRAINT ' + [name]  + ';'
FROM [sys].[default_constraints]
WHERE [parent_object_id] = OBJECT_ID('$tableName')
AND [parent_column_id] = COLUMNPROPERTY(OBJECT_ID('$tableName'), 'ticket_id', 'ColumnId')
AND [type] = 'D'

EXECUTE sp_executesql @sql
SQL;

            $this->execute($sql);
        }

        $this->dropTable('{{%ticket_attachment_ticket}}');
        $this->dropTable('{{%ticket_attachment_comment}}');
    }

    /**
     * @param string $driver
     * @return boolean
     */
    protected function isMSSQL()
    {
        return $this->db->driverName === 'mssql' || $this->db->driverName === 'sqlsrv' || $this->db->driverName === 'dblib';
    }
}
