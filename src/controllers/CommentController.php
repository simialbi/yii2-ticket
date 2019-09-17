<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\ticket\models\Attachment;
use simialbi\yii2\ticket\models\Comment;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\models\Topic;
use simialbi\yii2\ticket\Module;
use simialbi\yii2\ticket\TicketEvent;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Class CommentController
 *
 * @property-read Module $module
 */
class CommentController extends Controller
{
    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['updateTicket'],
                        'roleParams' => function () {
                            return ['ticket' => Ticket::findOne(Yii::$app->request->get('ticketId'))];
                        }
                    ]
                ]
            ]
        ];
    }

    /**
     * @return string
     */
    public function actionCreate()
    {
        $model = new Comment();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $ticket = $model->ticket;
            $ticket->setScenario(Ticket::SCENARIO_COMMENT);
            $attachments = Yii::$app->request->getBodyParam('attachments', []);

            $ticket->load(Yii::$app->request->post());

            $isResolved = false;
            if ($ticket->status === Ticket::STATUS_RESOLVED && $ticket->isAttributeChanged('status')) {
                $isResolved = true;
            }
            if ($ticket->save() && $isResolved) {
                $this->module->trigger(Module::EVENT_TICKET_RESOLVED, new TicketEvent([
                    'ticket' => $model,
                    'user' => $model->author
                ]));
            }

            $this->module->trigger(Module::EVENT_TICKET_COMMENTED, new TicketEvent([
                'ticket' => $model,
                'user' => $model->author
            ]));

            if (!empty($attachments)) {
                foreach ($attachments as $attachmentId) {
                    $attachment = Attachment::findOne(['unique_id' => $attachmentId]);
                    $model->link('attachments', $attachment);
                }
            }

            if ($this->module->sendMails && Yii::$app->mailer) {
                $to = ($ticket->created_by == Yii::$app->user->id)
                    ? [$ticket->agent->email => $ticket->agent->name]
                    : [$ticket->author->email => $ticket->author->name];

                $topics = Topic::find()->select(['name', 'id'])->orderBy(['name' => SORT_ASC])->indexBy('id')->column();
                $users = ArrayHelper::map(
                    call_user_func([Yii::$app->user->identityClass, 'findIdentities']),
                    'id',
                    'name'
                );
                $from = ArrayHelper::getValue(
                    Yii::$app->params,
                    'senderEmail',
                    ['no-reply@' . Yii::$app->request->hostName => Yii::$app->name . ' robot']
                );
                Yii::$app->mailer->compose([
                    'html' => '@simialbi/yii2/ticket/mail/new-comment-in-ticket-html',
                    'text' => '@simialbi/yii2/ticket/mail/new-comment-in-ticket-text'
                ], [
                    'model' => $ticket,
                    'comment' => $model,
                    'topics' => $topics,
                    'users' => $users,
                    'statuses' => Module::getStatuses(),
                    'priorities' => Module::getPriorities()
                ])
                    ->setFrom($from)
                    ->setTo($to)
                    ->setSubject(Yii::t('simialbi/ticket/mail', 'Ticket updated: {id} {subject}', [
                        'id' => $ticket->id,
                        'subject' => $ticket->subject
                    ]))
                    ->send();
            }

            return $this->renderAjax('ticket-comments', [
                'ticket' => $model->ticket,
                'newComment' => new Comment([
                    'ticket_id' => $model->ticket_id
                ])
            ]);
        }

        return $this->renderAjax('ticket-comments', [
            'ticket' => $model->ticket,
            'newComment' => $model
        ]);
    }
}
