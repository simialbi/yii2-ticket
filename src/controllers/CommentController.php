<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\kanban\models\Link;
use simialbi\yii2\kanban\models\Task;
use simialbi\yii2\ticket\behaviors\SendMailBehavior;
use simialbi\yii2\ticket\behaviors\SendSmsBehavior;
use simialbi\yii2\ticket\CommentEvent;
use simialbi\yii2\ticket\models\Attachment;
use simialbi\yii2\ticket\models\Comment;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\models\Topic;
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

            $this->module->attachNotificationBehaviors(Topic::EVENT_ON_TICKET_COMMENT, $ticket);

            $attachments = Yii::$app->request->getBodyParam('attachments', []);

            $ticket->load(Yii::$app->request->post());
            if (!$ticket->assigned_to) {
                if (array_key_exists(Yii::$app->user->id, $ticket->topic->agents)) {
                    $ticket->assigned_to = (string)Yii::$app->user->id;
                }
            }

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
                        $task->status = Task::STATUS_DONE;
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

                $ticket->trigger(Ticket::EVENT_AFTER_ADD_COMMENT, new CommentEvent([
                    'comment' => $model,
                    'ticket' => $ticket,
                    'user' => $model->author,
                    'gotClosed' => $isResolved
                ]));
            }

            $this->module->trigger(Module::EVENT_TICKET_COMMENTED, new CommentEvent([
                'comment' => $model,
                'ticket' => $ticket,
                'user' => $model->author,
                'gotClosed' => $isResolved
            ]));

            if (!empty($attachments)) {
                foreach ($attachments as $attachmentId) {
                    $attachment = Attachment::findOne(['unique_id' => $attachmentId]);
                    $model->link('attachments', $attachment);

                    if ($this->module->kanbanModule && ($task = $ticket->task)) {
                        $link = new Link([
                            'task_id' => $task->id,
                            'url' => Yii::$app->request->hostInfo . $attachment->path,
                            'created_by' => $attachment->created_by,
                            'updated_by' => $attachment->updated_by,
                            'created_at' => $attachment->created_at,
                            'updated_at' => $attachment->updated_at,
                        ]);
                        $link->save();
                    }
                }
            }

            return $this->renderAjax('ticket-comments', [
                'ticket' => $model->ticket,
                'newComment' => new Comment([
                    'ticket_id' => $model->ticket_id
                ]),
                'richTextFields' => $this->module->richTextFields
            ]);
        }

        return $this->renderAjax('ticket-comments', [
            'ticket' => $model->ticket,
            'newComment' => $model,
            'richTextFields' => $this->module->richTextFields
        ]);
    }
}
