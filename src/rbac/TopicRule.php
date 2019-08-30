<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\rbac;

use yii\helpers\ArrayHelper;
use yii\rbac\Rule;

/**
 * Class TopicRule
 * @package simialbi\yii2\ticket\rbac
 */
class TopicRule extends Rule
{
    /**
     * {@inheritDoc}
     */
    public $name = 'isResponsible';


    /**
     * {@inheritDoc}
     */
    public function execute($user, $item, $params)
    {
        $agents = ArrayHelper::getValue($params, ['ticket', 'topic', 'agents'], []);
        return in_array($user, $agents);
    }
}
