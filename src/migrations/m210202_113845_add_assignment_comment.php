<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace simialbi\yii2\ticket\migrations;

use yii\db\Migration;

class m210202_113845_add_assignment_comment extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%ticket_ticket}}',
            'assignment_comment',
            $this->text()->null()->defaultValue(null)->after('description')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ticket_ticket}}', 'assignment_comment');
    }
}
