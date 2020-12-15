<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace simialbi\yii2\ticket\migrations;

use yii\db\Migration;

class m201215_081934_add_notification_options_to_topic extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%ticket_topic}}',
            'on_new_ticket',
            $this->getColumnDefinition('on_new_ticket', 'status')
        );
        $this->addColumn(
            '{{%ticket_topic}}',
            'on_ticket_update',
            $this->getColumnDefinition('on_ticket_update', 'on_new_ticket')
        );
        $this->addColumn(
            '{{%ticket_topic}}',
            'on_ticket_assignment',
            $this->getColumnDefinition('on_ticket_assignment', 'on_ticket_update')
        );
        $this->addColumn(
            '{{%ticket_topic}}',
            'on_ticket_resolution',
            $this->getColumnDefinition('on_ticket_resolution', 'on_ticket_assignment')
        );
        $this->addColumn(
            '{{%ticket_topic}}',
            'on_ticket_comment',
            $this->getColumnDefinition('on_ticket_comment', 'on_ticket_resolution')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ticket_topic}}', 'on_ticket_comment');
        $this->dropColumn('{{%ticket_topic}}', 'on_ticket_resolution');
        $this->dropColumn('{{%ticket_topic}}', 'on_ticket_assignment');
        $this->dropColumn('{{%ticket_topic}}', 'on_ticket_update');
        $this->dropColumn('{{%ticket_topic}}', 'on_new_ticket');
    }

    /**
     * Generate column definition based on driver
     *
     * @param string $name
     * @param string $after
     *
     * @return string
     */
    private function getColumnDefinition($name, $after = '')
    {
        return $this->isMSSQL()
            ? "VARCHAR(4) NULL DEFAULT NULL CHECK([[$name]] IN ('sms', 'mail'))"
            : 'ENUM(\'sms\', \'mail\') NULL DEFAULT NULL' . (!empty($after) ? " AFTER [[$after]]" : '');
    }

    /**
     * Returns true if is ms sql based driver
     *
     * @return boolean
     */
    private function isMSSQL()
    {
        return self::getDb()->driverName === 'mssql' || self::getDb()->driverName === 'dblib' || self::getDb()->driverName === 'sqlsrv';
    }
}
