<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\rbac;

use yii\rbac\Rule;

/**
 * Check if ticket is assigned to specified agent
 */
class AssigneeRule extends Rule
{
    /**
     * {@inheritDoc}
     */
    public $name = 'isAssigned';


    /**
     * {@inheritDoc}
     */
    public function execute($user, $item, $params)
    {
        return isset($params['ticket']) && ($params['ticket']->assigned_to == $user || $params['ticket']->assigned_to === null);
    }
}
