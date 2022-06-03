<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket;

use simialbi\yii2\models\UserInterface;
use simialbi\yii2\ticket\behaviors\SendMailBehavior;
use simialbi\yii2\ticket\behaviors\SendSmsBehavior;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\models\Topic;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class Module
 * @package simialbi\yii2\ticket
 */
class Module extends \simialbi\yii2\base\Module
{
    const EVENT_TICKET_CREATED = 'ticketCreated';
    const EVENT_TICKET_UPDATED = 'ticketUpdated';
    const EVENT_TICKET_ASSIGNED = 'ticketAssigned';
    const EVENT_TICKET_RESOLVED = 'ticketResolved';
    const EVENT_TICKET_COMMENTED = 'ticketCommented';

    /**
     * @var string|boolean|array Kanban module name if exits. Will be auto detected if not set
     */
    public $kanbanModule;

    /**
     * {@inheritDoc}
     */
    public $defaultRoute = 'ticket';

    /**
     * @var boolean Use rich text fields in ticket comments
     */
    public $richTextFields = false;

    /**
     * @var \simialbi\yii2\sms\ProviderInterface|string|array the provider object or the ID of the provider application
     * component that is used to send sms
     */
    public $smsProvider;

    /**
     * @var boolean if set to true, agents can assign tickets to non-agents
     */
    public $canAssignTicketsToNonAgents = false;

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
        if (!isset($this->kanbanModule) && Yii::$app->hasModule('kanban')) {
            $this->kanbanModule = 'kanban';
        } elseif (isset($this->kanbanModule) && is_string($this->kanbanModule) && !Yii::$app->hasModule($this->kanbanModule)) {
            $this->kanbanModule = false;
        } elseif (is_array($this->kanbanModule) && isset($this->kanbanModule['class'])) {
            $this->setModule('kanban', $this->kanbanModule);
            $this->kanbanModule = $this->id . '/kanban';
        }
        if (is_array($this->smsProvider) && isset($this->smsProvider['class'])) {
            $this->setModule('ticketSmsProvider', $this->smsProvider);
            $this->smsProvider = 'ticketSmsProvider';
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

    /**
     * Get all users with role ticketAgent
     * @param null|Topic $topic filter by topic
     * @return array
     */
    public static function getAgents($topic = null)
    {
        $ret = [];
        $users = call_user_func([Yii::$app->user->identityClass, 'findIdentities']);

        if (!Yii::$app->authManager) {
            return ArrayHelper::map($users, 'id', 'name');
        }

        foreach ($users as $user) {
            // check if is agent
            if (Yii::$app->authManager->checkAccess($user->id, 'ticketAgent')) {
                if (!is_null($topic) && array_key_exists($user->id, $topic->agents)) {
                    $ret[] = $user;
                } else {
                    $ret[] = $user;
                }
            }
        }

        return ArrayHelper::map($ret, 'id', 'name');
    }

    /**
     * Attaches the behaviors to notify the users on specific events
     * @param string $event One of the EVENT_... const-variables of the Topic
     * @param Ticket $ticket
     * @param array $options Provide array to override the behaviors array. Must be a subarray with the behavior-name as key
     * @return void
     * @throws \Exception
     */
    public function attachNotificationBehaviors($event, &$ticket, $options = [])
    {
        if ($ticket->topic->hasNotification($event, Topic::BEHAVIOR_MAIL)) {
            $behavior = [
                'class' => SendMailBehavior::class,
                'isRichText' => $this->richTextFields,
                'agentsToInform' => function ($ticket) {
                    /** @var $ticket Ticket */
                    $recipients = [];
                    foreach ($ticket->topic->agents as $agent) {
                        if (!empty($agent->email)) {
                            $recipients[$agent->email] = $agent->name;
                        }
                    }

                    return $recipients;
                }
            ];
            $behavior = ArrayHelper::merge($behavior, ArrayHelper::getValue($options, Topic::BEHAVIOR_MAIL, []));
            $ticket->attachBehavior('sendMail', $behavior);
        }
        if ($ticket->topic->hasNotification($event, Topic::BEHAVIOR_SMS)) {
            $behavior = [
                'class' => SendSmsBehavior::class,
                'agentsToInform' => function ($ticket) {
                    /** @var $ticket Ticket */
                    $recipients = [];
                    foreach ($ticket->topic->agents as $agent) {
                        if (!empty($agent->mobile)) {
                            $recipients[] = $agent->mobile;
                        }
                    }

                    return $recipients;
                },
                'provider' => $this->smsProvider
            ];
            $behavior = ArrayHelper::merge($behavior, ArrayHelper::getValue($options, Topic::BEHAVIOR_SMS, []));
            $ticket->attachBehavior('sendSms', $behavior);
        }
    }
}
