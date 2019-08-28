<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket;

use Yii;

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

        if (!Yii::$app->hasModule('gridview')) {
            $this->setModule('gridview', [
                'class' => 'kartik\grid\Module',
                'exportEncryptSalt' => 'ror_HTbRh0Ad7K7DqhAtZOp50GKyia4c',
                'i18n' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@kvgrid/messages',
                    'forceTranslation' => true
                ]
            ]);
        }

        parent::init();
    }
}
