<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace simialbi\yii2\ticket\behaviors;

use simialbi\yii2\sms\ProviderInterface;
use simialbi\yii2\ticket\CommentEvent;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\models\Topic;
use simialbi\yii2\ticket\Module;
use Yii;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;

class SendSmsBehavior extends Behavior
{
    use SendBehaviorTrait;

    /**
     * @var string|array|\Closure The ticket's agent's mobile phone number property (ticket as base model)
     */
    public $agentNumberProperty = ['agent', 'mobile'];
    /**
     * @var string|array|\Closure The ticket's author's mobile phone number property (ticket as base model)
     */
    public $authorNumberProperty = ['author', 'number'];
    /**
     * @var ProviderInterface|string the provider object or the ID of the provider application component that is
     * used to send sms
     */
    public $provider;

    /**
     * {@inheritDoc}
     */
    public function events()
    {
        return [
            Ticket::EVENT_AFTER_INSERT => 'afterInsert',
            Ticket::EVENT_AFTER_ASSIGN => 'afterAssign',
            Ticket::EVENT_AFTER_CLOSE => 'afterClose',
            Ticket::EVENT_AFTER_ADD_COMMENT => 'afterComment'
        ];
    }

    /**
     * Sends information sms after a ticket was created
     *
     * @return boolean
     * @throws \Exception
     */
    public function afterInsert()
    {
        if (empty($this->agentsToInform)) {
            return false;
        }

        $recipients = (is_callable($this->agentsToInform))
            ? call_user_func($this->agentsToInform, $this->owner)
            : $this->agentsToInform;

        return $this->sendSms(
            '@simialbi/yii2/ticket/sms/new-ticket',
            $recipients,
            Yii::t('simialbi/ticket/mail', 'New Ticket: {id} {subject}', [
                'id' => ArrayHelper::getValue($this->owner, 'id'),
                'subject' => ArrayHelper::getValue($this->owner, 'subject')
            ])
        );
    }

    /**
     * Sends an information sms to the agent which was assigned to the ticket
     *
     * @return boolean
     * @throws \Exception
     */
    public function afterAssign()
    {
        $email = ArrayHelper::getValue($this->owner, $this->agentNumberProperty);
        $name = ArrayHelper::getValue($this->owner, $this->agentNameProperty, $email);
        return $this->sendSms(
            '@simialbi/yii2/ticket/sms/you-were-assigned',
            [$email => $name],
            Yii::t('simialbi/ticket/mail', 'You\'ve been assigned to a ticket: {id} {subject}', [
                'id' => ArrayHelper::getValue($this->owner, 'id'),
                'subject' => ArrayHelper::getValue($this->owner, 'subject')
            ])
        );
    }

    /**
     * Sends an information sms after a comment was created in a ticket
     *
     * @param CommentEvent $event
     *
     * @return boolean
     * @throws \Exception
     */
    public function afterComment($event)
    {
        if (ArrayHelper::getValue($this->owner, 'created_by') == Yii::$app->user->id) {
            $number = ArrayHelper::getValue($this->owner, $this->agentNumberProperty);
        } else {
            $number = ArrayHelper::getValue($this->owner, $this->authorNumberProperty);
        }
        return $this->sendSms(
            '@simialbi/yii2/ticket/sms/new-comment-in-ticket',
            $number,
            Yii::t('simialbi/ticket/mail', 'Ticket updated: {id} {subject}', [
                'id' => ArrayHelper::getValue($this->owner, 'id'),
                'subject' => ArrayHelper::getValue($this->owner, 'subject')
            ]),
            null,
            ['comment' => $event->comment]
        );
    }

    /**
     * Sends an information sms after closing a ticket
     *
     * @return boolean
     * @throws \Exception
     */
    public function afterClose()
    {
        $recipients = [];
        ArrayHelper::setValue(
            $recipients,
            [ArrayHelper::getValue($this->owner, $this->agentNumberProperty)],
            ArrayHelper::getValue($this->owner, $this->agentNameProperty)
        );
        return $this->sendSms(
            '@simialbi/yii2/ticket/sms/ticket-resolved',
            ArrayHelper::getValue($this->owner, $this->agentNumberProperty),
            Yii::t('simialbi/ticket/mail', 'Ticket updated: {id} {subject}', [
                'id' => ArrayHelper::getValue($this->owner, 'id'),
                'subject' => ArrayHelper::getValue($this->owner, 'subject')
            ])
        );
    }

    /**
     * Creates a new message instance and composes its body content via view rendering and send the sms.
     *
     * @param string $view the view to be used for rendering the message body. This can be:
     *
     * - a string, which represents the view name or [path alias](guide:concept-aliases) for rendering the HTML body of the email.
     *   In this case, the text body will be generated by applying `strip_tags()` to the HTML body.
     * @param string|array $to receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @param string|null $subject message subject
     * @param string|array|null $from sender email address.
     * You may pass an array of addresses if this message is from multiple people.
     * You may also specify sender name in addition to email address using format:
     * `[email => name]`.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @return boolean
     * @throws \Exception
     */
    protected function sendSms($view, $to, $subject = null, $from = null, array $params = [])
    {
        /** @var ProviderInterface $provider */
        if (is_string($this->provider)) {
            $provider = Yii::$app->get($this->provider, false);
        } else {
            $provider = $this->provider;
        }
        if (!$provider) {
            return false;
        }

        $topics = Topic::find()->select(['name', 'id'])->orderBy(['name' => SORT_ASC])->indexBy('id')->column();
        $users = ArrayHelper::map(
            call_user_func([Yii::$app->user->identityClass, 'findIdentities']),
            'id',
            'name'
        );
        $params = ArrayHelper::merge([
            'model' => $this->owner,
            'topics' => $topics,
            'users' => $users,
            'statuses' => Module::getStatuses(),
            'priorities' => Module::getPriorities()
        ], $params);

        return $provider->compose($view, $params)->setFrom($from)->setTo($to)->setSubject($subject)->send();
    }
}
