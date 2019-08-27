<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket;

/**
 * Class Module
 * @package simialbi\yii2\ticket
 */
class Module extends \simialbi\yii2\base\Module
{
    /**
     * {@inheritDoc}
     */
    public $defaultRoute = 'ticket';

    /**
     * {@inheritDoc}
     * @throws \ReflectionException
     */
    public function init()
    {
        $this->registerTranslations();

        parent::init();
    }
}
