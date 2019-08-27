<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\rbac;

use yii\rbac\Rule;

/**
 * Check if user is the author of the specified ticket
 */
class AuthorRule extends Rule
{
    /**
     * {@inheritDoc}
     */
    public $name = 'isAuthor';


    /**
     * {@inheritDoc}
     */
    public function execute($user, $item, $params)
    {
        return isset($params['ticket']) ? $params['ticket']->created_by ==  $user : false;
    }
}
