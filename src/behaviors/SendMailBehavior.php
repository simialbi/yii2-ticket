<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\behaviors;

use simialbi\yii2\ticket\CommentEvent;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\models\Topic;
use simialbi\yii2\ticket\Module;
use Yii;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;

class SendMailBehavior extends Behavior
{
    /**
     * @var string|array|\Closure The ticket's id property
     */
    public $idProperty = 'id';
    /**
     * @var string|array|\Closure The ticket's subject property
     */
    public $subjectProperty = 'subject';
    /**
     * @var string|array|\Closure The ticket's "created by" property
     */
    public $createdByProperty = 'created_by';
    /**
     * @var string|array|\Closure The ticket's agent's email address property (ticket as base model)
     */
    public $agentEmailProperty = ['agent', 'email'];
    /**
     * @var string|array|\Closure The ticket's agent's name property (ticket as base model)
     */
    public $agentNameProperty = ['agent', 'name'];
    /**
     * @var string|array|\Closure The ticket's author's email address property (ticket as base model)
     */
    public $authorEmailProperty = ['author', 'email'];
    /**
     * @var string|array|\Closure The ticket's author's name property (ticket as base model)
     */
    public $authorNameProperty = ['author', 'name'];
    /**
     * @var \Closure|array An array or function which returns an array of agents to inform after a ticket was created
     */
    public $agentsToInform;

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
     * Sends information mail after a ticket was created
     *
     * @return boolean
     */
    public function afterInsert()
    {
        if (empty($this->agentsToInform)) {
            return false;
        }

        $recipients = (is_callable($this->agentsToInform))
            ? call_user_func($this->agentsToInform, $this->owner)
            : $this->agentsToInform;

        return $this->sendMail(
            [
                'html' => '@simialbi/yii2/ticket/mail/new-ticket-html',
                'text' => '@simialbi/yii2/ticket/mail/new-ticket-text'
            ],
            $recipients,
            Yii::t('simialbi/ticket/mail', 'New Ticket: {id} {subject}', [
                'id' => ArrayHelper::getValue($this->owner, $this->idProperty),
                'subject' => ArrayHelper::getValue($this->owner, $this->subjectProperty)
            ])
        );
    }

    /**
     * Sends an information mail to the agent which was assigned to the ticket
     *
     * @return boolean
     */
    public function afterAssign()
    {
        $email = ArrayHelper::getValue($this->owner, $this->agentEmailProperty);
        $name = ArrayHelper::getValue($this->owner, $this->agentNameProperty, $email);
        return $this->sendMail(
            [
                'html' => '@simialbi/yii2/ticket/mail/you-were-assigned-html',
                'text' => '@simialbi/yii2/ticket/mail/you-were-assigned-text'
            ],
            [$email => $name],
            Yii::t('simialbi/ticket/mail', 'You\'ve been assigned to a ticket: {id} {subject}', [
                'id' => ArrayHelper::getValue($this->owner, $this->idProperty),
                'subject' => ArrayHelper::getValue($this->owner, $this->subjectProperty)
            ])
        );
    }

    /**
     * Sends an information mail after a comment was created in a ticket
     *
     * @param CommentEvent $event
     *
     * @return boolean
     */
    public function afterComment($event)
    {
        if (ArrayHelper::getValue($this->owner, $this->createdByProperty) == Yii::$app->user->id) {
            $email = ArrayHelper::getValue($this->owner, $this->agentEmailProperty);
            $name = ArrayHelper::getValue($this->owner, $this->agentNameProperty, $email);
        } else {
            $email = ArrayHelper::getValue($this->owner, $this->authorEmailProperty);
            $name = ArrayHelper::getValue($this->owner, $this->authorNameProperty, $email);
        }
        return $this->sendMail(
            [
                'html' => '@simialbi/yii2/ticket/mail/new-comment-in-ticket-html',
                'text' => '@simialbi/yii2/ticket/mail/new-comment-in-ticket-text'
            ],
            [$email => $name],
            Yii::t('simialbi/ticket/mail', 'Ticket updated: {id} {subject}', [
                'id' => ArrayHelper::getValue($this->owner, $this->idProperty),
                'subject' => ArrayHelper::getValue($this->owner, $this->subjectProperty)
            ]),
            null,
            ['comment' => $event->comment]
        );
    }

    /**
     * Sends an information mail after closing a ticket
     *
     * @return boolean
     */
    public function afterClose()
    {
        $recipients = [];
        ArrayHelper::setValue(
            $recipients,
            [ArrayHelper::getValue($this->owner, $this->agentEmailProperty)],
            ArrayHelper::getValue($this->owner, $this->agentNameProperty)
        );
        ArrayHelper::setValue(
            $recipients,
            [ArrayHelper::getValue($this->owner, $this->authorEmailProperty)],
            ArrayHelper::getValue($this->owner, $this->authorNameProperty)
        );
        return $this->sendMail(
            [
                'html' => '@simialbi/yii2/ticket/mail/ticket-resolved-html',
                'text' => '@simialbi/yii2/ticket/mail/ticket-resolved-text'
            ],
            $recipients,
            Yii::t('simialbi/ticket/mail', 'Ticket updated: {id} {subject}', [
                'id' => ArrayHelper::getValue($this->owner, $this->idProperty),
                'subject' => ArrayHelper::getValue($this->owner, $this->subjectProperty)
            ])
        );
    }

    /**
     * Creates a new message instance and composes its body content via view rendering and send the mail.
     *
     * @param string|array $view the view to be used for rendering the message body. This can be:
     *
     * - a string, which represents the view name or [path alias](guide:concept-aliases) for rendering the HTML body of the email.
     *   In this case, the text body will be generated by applying `strip_tags()` to the HTML body.
     * - an array with 'html' and/or 'text' elements. The 'html' element refers to the view name or path alias
     *   for rendering the HTML body, while 'text' element is for rendering the text body. For example,
     *   `['html' => 'contact-html', 'text' => 'contact-text']`.
     * @param string|array $to receiver email address.
     * You may pass an array of addresses if multiple recipients should receive this message.
     * You may also specify receiver name in addition to email address using format:
     * `[email => name]`.
     * @param string $subject message subject
     * @param string|array $from sender email address.
     * You may pass an array of addresses if this message is from multiple people.
     * You may also specify sender name in addition to email address using format:
     * `[email => name]`.
     * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
     * @return boolean
     */
    protected function sendMail($view, $to, $subject, $from = null, array $params = [])
    {
        if (!Yii::$app->mailer) {
            return false;
        }
        if (empty($from)) {
            $from = ArrayHelper::getValue(
                Yii::$app->params,
                'senderEmail',
                ['no-reply@' . Yii::$app->request->hostName => Yii::$app->name . ' robot']
            );
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

        return Yii::$app->mailer->compose($view, $params)->setFrom($from)->setTo($to)->setSubject($subject)->send();
    }
}
