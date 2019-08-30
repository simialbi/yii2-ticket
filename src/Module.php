<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket;

use simialbi\yii2\models\UserInterface;
use simialbi\yii2\ticket\models\Ticket;
use Yii;
use yii\base\InvalidConfigException;

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
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->registerTranslations();

        $identity = new Yii::$app->user->identityClass;
        if (!($identity instanceof UserInterface)) {
            throw new InvalidConfigException('The "identityClass" must extend "simialbi\yii2\models\UserInterface"');
        }
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

    /**
     * Get ticket priorities
     *
     * @return array
     */
    public static function getPriorities()
    {
        return [
            Ticket::PRIORITY_LOW => Yii::t('simialbi/ticket/priority', 'Low'),
            Ticket::PRIORITY_NORMAL => Yii::t('simialbi/ticket/priority', 'Normal'),
            Ticket::PRIORITY_HIGH => Yii::t('simialbi/ticket/priority', 'High'),
            Ticket::PRIORITY_EMERGENCY => Yii::t('simialbi/ticket/priority', 'Emergency')
        ];
    }

    /**
     * Get ticket priorities
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            Ticket::STATUS_RESOLVED => Yii::t('simialbi/ticket/status', 'Resolved'),
            Ticket::STATUS_IN_PROGRESS => Yii::t('simialbi/ticket/status', 'In progress'),
            Ticket::STATUS_ASSIGNED => Yii::t('simialbi/ticket/status', 'Assigned'),
            Ticket::STATUS_OPEN => Yii::t('simialbi/ticket/status', 'Open'),
            Ticket::STATUS_LATE => Yii::t('simialbi/ticket/status', 'Late'),
        ];
    }
}
