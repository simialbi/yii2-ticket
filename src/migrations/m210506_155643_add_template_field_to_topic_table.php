<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace simialbi\yii2\ticket\migrations;

use yii\db\Migration;

/**
 * Class m210506_155643_add_template_field_to_topic_table
 * @package simialbi\yii2\ticket\migrations
 */
class m210506_155643_add_template_field_to_topic_table extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%ticket_topic}}',
            'template',
            $this->text()->null()->defaultValue(null)->after('new_ticket_status')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%ticket_topic}}', 'template');
    }
}
