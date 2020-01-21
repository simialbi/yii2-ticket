<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\ticket\behaviors\SendMailBehavior;
use simialbi\yii2\ticket\models\Attachment;
use simialbi\yii2\ticket\models\Comment;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\Module;
use simialbi\yii2\ticket\TicketEvent;
use Yii;
use yii\filters\AccessControl;
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
            if ($this->module->sendMails) {
                $ticket->attachBehavior('sendMail', [
                    'class' => SendMailBehavior::class,
                    'agentsToInform' => function ($model) {
                        /** @var $model Ticket */
                        $recipients = [];
                        foreach ($model->topic->agents as $agent) {
                            $recipients[$agent->email] = $agent->name;
                        }

                        return $recipients;
                    }
                ]);
            }
            $attachments = Yii::$app->request->getBodyParam('attachments', []);

            $ticket->load(Yii::$app->request->post());

            $isResolved = false;
            if ((int)$ticket->status === Ticket::STATUS_RESOLVED && $ticket->isAttributeChanged('status')) {
                $isResolved = true;
            }
            if ($ticket->save()) {
                if ($this->module->kanbanModule && ($task = $ticket->task)) {
                    $taskComment = new \simialbi\yii2\kanban\models\Comment([
                        'task_id' => $task->id,
                        'created_at' => $model->created_at,
                        'created_by' => $model->created_by,
                        'text' => $model->text
                    ]);
                    $taskComment->detachBehavior('timestamp');
                    $taskComment->detachBehavior('blameable');
                    $taskComment->save();

                    if ($isResolved) {
                        $task->status = \simialbi\yii2\kanban\models\Task::STATUS_DONE;
                        $task->save();
                    }
                }

                if ($isResolved) {
                    $this->module->trigger(Module::EVENT_TICKET_RESOLVED, new TicketEvent([
                        'ticket' => $ticket,
                        'user' => $model->author,
                        'gotClosed' => true
                    ]));
                }

                $ticket->trigger(Ticket::EVENT_AFTER_ADD_COMMENT, new TicketEvent([
                    'ticket' => $ticket,
                    'user' => $model->author,
                    'gotClosed' => $isResolved
                ]));
            }

            $this->module->trigger(Module::EVENT_TICKET_COMMENTED, new TicketEvent([
                'ticket' => $ticket,
                'user' => $model->author,
                'gotClosed' => $isResolved
            ]));

            if (!empty($attachments)) {
                foreach ($attachments as $attachmentId) {
                    $attachment = Attachment::findOne(['unique_id' => $attachmentId]);
                    $model->link('attachments', $attachment);
                }
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
