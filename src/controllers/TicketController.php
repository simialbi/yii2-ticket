<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\kanban\models\TaskUserAssignment;
use simialbi\yii2\ticket\behaviors\SendMailBehavior;
use simialbi\yii2\ticket\behaviors\SendSmsBehavior;
use simialbi\yii2\ticket\models\Attachment;
use simialbi\yii2\ticket\models\Comment;
use simialbi\yii2\ticket\models\CreateTaskForm;
use simialbi\yii2\ticket\models\SearchTicket;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\models\Topic;
use simialbi\yii2\ticket\Module;
use simialbi\yii2\ticket\TicketEvent;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class TicketController
 *
 * @property-read Module $module
 */
class TicketController extends Controller
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
                        'actions' => ['index'],
                        'roles' => ['@']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['createTicket']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'roles' => ['updateTicket']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['assign'],
                        'roles' => ['assignTicket'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['take'],
                        'roles' => ['takeTicket'],
                        'roleParams' => function () {
                            return ['ticket' => $this->findModel(Yii::$app->request->get('id'))];
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['viewTicket'],
                        'roleParams' => function () {
                            return ['ticket' => $this->findModel(Yii::$app->request->get('id'))];
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['close'],
                        'roles' => ['closeTicket'],
                        'roleParams' => function () {
                            return ['ticket' => $this->findModel(Yii::$app->request->get('id'))];
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create-task'],
                        'roles' => ['administrateTicket'],
                        'roleParams' => function () {
                            return ['ticket' => $this->findModel(Yii::$app->request->get('id'))];
                        }
                    ]
                ]
            ]
        ];
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Ticket the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Ticket::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $statuses = Module::getStatuses();
        unset($statuses[Ticket::STATUS_RESOLVED]);
        $searchModel = new SearchTicket([
            'status' => array_keys($statuses)
        ]);
        $userId = null;
        if (!Yii::$app->user->can('ticketAgent')) {
            $userId = Yii::$app->user->id;
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $userId);

        $topics = Topic::find()->select(['name', 'id'])->orderBy(['name' => SORT_ASC])->indexBy('id')->column();
        $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'topics' => $topics,
            'users' => $users,
            'hasKanban' => (boolean)$this->module->kanbanModule,
            'statuses' => Module::getStatuses(),
            'priorities' => Module::getPriorities()
        ]);
    }

    /**
     * View ticket
     * @param integer $id
     * @return string
     *
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'hasKanban' => (boolean)$this->module->kanbanModule,
            'richTextFields' => $this->module->richTextFields,
            'statuses' => Module::getStatuses(),
            'priorities' => Module::getPriorities()
        ]);
    }

    /**
     * Create a new ticket
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionCreate()
    {
        $model = new Ticket([
            'source_id' => 1,
            'priority' => Ticket::PRIORITY_NORMAL,
            'created_by' => Yii::$app->user->id
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->topic->new_ticket_assign_to) {
                $model->assigned_to = $model->topic->new_ticket_assign_to;
            }
            if ($model->topic->new_ticket_status) {
                $model->status = $model->topic->new_ticket_status;
            }
            if ($model->topic->on_new_ticket === Topic::BEHAVIOR_MAIL) {
                $model->attachBehavior('sendMail', [
                    'class' => SendMailBehavior::class,
                    'isRichText' => $this->module->richTextFields,
                    'agentsToInform' => function ($model) {
                        /** @var $model Ticket */
                        $recipients = [];
                        foreach ($model->topic->agents as $agent) {
                            if (!empty($agent->email)) {
                                $recipients[$agent->email] = $agent->name;
                            }
                        }

                        return $recipients;
                    }
                ]);
            } elseif ($model->topic->on_new_ticket === Topic::BEHAVIOR_SMS) {
                $model->attachBehavior('sendSms', [
                    'class' => SendSmsBehavior::class,
                    'agentsToInform' => function ($model) {
                        /** @var $model Ticket */
                        $recipients = [];
                        foreach ($model->topic->agents as $agent) {
                            if (!empty($agent->mobile)) {
                                $recipients[] = $agent->mobile;
                            }
                        }

                        return $recipients;
                    },
                    'provider' => $this->module->smsProvider
                ]);
            }
            $model->save();
            $attachments = Yii::$app->request->getBodyParam('attachments', []);

            if (!empty($attachments)) {
                foreach ($attachments as $attachmentId) {
                    $attachment = Attachment::findOne(['unique_id' => $attachmentId]);
                    $model->link('attachments', $attachment);
                }
            }

            $this->module->trigger(Module::EVENT_TICKET_CREATED, new TicketEvent([
                'ticket' => $model,
                'user' => $model->author
            ]));

            return $this->redirect(['index']);
        }

        $topics = Topic::find()->orderBy(['name' => SORT_ASC])->where(['status' => true])->all();
        $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');

        return $this->render('create', [
            'model' => $model,
            'topics' => $topics,
            'users' => $users,
            'priorities' => Module::getPriorities(),
            'richTextFields' => $this->module->richTextFields
        ]);
    }

    /**
     * Update a ticket
     * @param integer $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->topic->on_ticket_update === Topic::BEHAVIOR_MAIL) {
            $model->attachBehavior('sendMail', [
                'class' => SendMailBehavior::class,
                'isRichText' => $this->module->richTextFields,
                'agentsToInform' => function ($model) {
                    /** @var $model Ticket */
                    $recipients = [];
                    foreach ($model->topic->agents as $agent) {
                        if (!empty($agent->email)) {
                            $recipients[$agent->email] = $agent->name;
                        }
                    }

                    return $recipients;
                }
            ]);
        } elseif ($model->topic->on_ticket_update === Topic::BEHAVIOR_SMS) {
            $model->attachBehavior('sendSms', [
                'class' => SendSmsBehavior::class,
                'agentsToInform' => function ($model) {
                    /** @var $model Ticket */
                    $recipients = [];
                    foreach ($model->topic->agents as $agent) {
                        if (!empty($agent->mobile)) {
                            $recipients[] = $agent->mobile;
                        }
                    }

                    return $recipients;
                },
                'provider' => $this->module->smsProvider
            ]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->module->trigger(Module::EVENT_TICKET_UPDATED, new TicketEvent([
                'ticket' => $model,
                'user' => $model->updater
            ]));

            return $this->redirect(['index']);
        }

        $topics = Topic::find()->orderBy(['name' => SORT_ASC])->where(['status' => true])->all();
        $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');

        return $this->render('update', [
            'model' => $model,
            'topics' => $topics,
            'users' => $users,
            'priorities' => Module::getPriorities(),
            'richTextFields' => $this->module->richTextFields
        ]);
    }

    /**
     * Assign a ticket
     * @param integer $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAssign($id)
    {
        $model = $this->findModel($id);
        if ($model->due_date) {
            $model->due_date = Yii::$app->formatter->asDate($model->due_date, 'dd.MM.yyyy');
        }
        $model->scenario = $model::SCENARIO_ASSIGN;

        if ($model->topic->on_ticket_assignment === Topic::BEHAVIOR_MAIL) {
            $model->attachBehavior('sendMail', [
                'class' => SendMailBehavior::class,
                'isRichText' => $this->module->richTextFields,
                'agentsToInform' => function ($model) {
                    /** @var $model Ticket */
                    $recipients = [];
                    foreach ($model->topic->agents as $agent) {
                        if (!empty($agent->email)) {
                            $recipients[$agent->email] = $agent->name;
                        }
                    }

                    return $recipients;
                }
            ]);
        } elseif ($model->topic->on_ticket_assignment === Topic::BEHAVIOR_SMS) {
            $model->attachBehavior('sendSms', [
                'class' => SendSmsBehavior::class,
                'agentsToInform' => function ($model) {
                    /** @var $model Ticket */
                    $recipients = [];
                    foreach ($model->topic->agents as $agent) {
                        if (!empty($agent->mobile)) {
                            $recipients[] = $agent->mobile;
                        }
                    }

                    return $recipients;
                },
                'provider' => $this->module->smsProvider
            ]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($this->module->kanbanModule && ($task = $model->task)) {
                $assignment = new TaskUserAssignment([
                    'user_id' => (string)$model->assigned_to,
                    'task_id' => $task->id
                ]);
                $assignment->save();
            }

            $this->module->trigger(Module::EVENT_TICKET_ASSIGNED, new TicketEvent([
                'ticket' => $model,
                'user' => $model->agent
            ]));

            return $this->redirect(Yii::$app->request->referrer);
        }

        $users = ArrayHelper::map($model->topic->agents, 'id', 'name');
        if ($this->module->canAssignTicketsToNonAgents) {
            $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');
        }

        return $this->renderAjax('assign', [
            'model' => $model,
            'users' => $users
        ]);
    }

    /**
     * Take a ticket
     * @param integer $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionTake($id)
    {
        $model = $this->findModel($id);

        $model->assigned_to = (string)Yii::$app->user->id;
        $model->status = Ticket::STATUS_ASSIGNED;
        if ($model->save()) {
            if ($this->module->kanbanModule && ($task = $model->task)) {
                $assignment = new TaskUserAssignment([
                    'user_id' => (string)$model->assigned_to,
                    'task_id' => $task->id
                ]);
                $assignment->save();
            }
        }

        $this->module->trigger(Module::EVENT_TICKET_ASSIGNED, new TicketEvent([
            'ticket' => $model,
            'user' => $model->agent
        ]));

//        return $this->goBack();
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Close a ticket
     *
     * @param integer $id
     *
     * @return \yii\web\Response|string
     *
     * @throws NotFoundHttpException
     */
    public function actionClose($id)
    {
        if (Yii::$app->request->post()) {
            $model = $this->findModel($id);
            $model->status = Ticket::STATUS_RESOLVED;

            // comment if not empty
            if (ArrayHelper::getValue(Yii::$app->request->post(), 'comment') != null) {
                $comment = new Comment([
                    'ticket_id' => $model->id,
                    'text' => Yii::$app->request->post('comment')
                ]);
                if ($comment->save()) {
                    if ($this->module->kanbanModule && ($task = $model->task)) {
                        $taskComment = new \simialbi\yii2\kanban\models\Comment([
                            'task_id' => $task->id,
                            'created_at' => $comment->created_at,
                            'created_by' => $comment->created_by,
                            'text' => $comment->text
                        ]);
                        $taskComment->detachBehavior('timestamp');
                        $taskComment->detachBehavior('blameable');
                        $taskComment->save();
                    }
                }
            }

            if ($model->topic->on_ticket_resolution === Topic::BEHAVIOR_MAIL) {
                $model->attachBehavior('sendMail', [
                    'class' => SendMailBehavior::class,
                    'isRichText' => $this->module->richTextFields,
                    'agentsToInform' => function ($model) {
                        /** @var $model Ticket */
                        $recipients = [];
                        foreach ($model->topic->agents as $agent) {
                            if (!empty($agent->email)) {
                                $recipients[$agent->email] = $agent->name;
                            }
                        }

                        return $recipients;
                    }
                ]);
            } elseif ($model->topic->on_ticket_resolution === Topic::BEHAVIOR_SMS) {
                $model->attachBehavior('sendSms', [
                    'class' => SendSmsBehavior::class,
                    'agentsToInform' => function ($model) {
                        /** @var $model Ticket */
                        $recipients = [];
                        foreach ($model->topic->agents as $agent) {
                            if (!empty($agent->mobile)) {
                                $recipients[] = $agent->mobile;
                            }
                        }

                        return $recipients;
                    },
                    'provider' => $this->module->smsProvider
                ]);
            }

            if ($model->save()) {
                if ($this->module->kanbanModule && ($task = $model->task)) {
                    $task->status = \simialbi\yii2\kanban\models\Task::STATUS_DONE;
                    $task->save();
                }

                $this->module->trigger(Module::EVENT_TICKET_RESOLVED, new TicketEvent([
                    'ticket' => $model,
                    'user' => $model->author
                ]));
            }

            return $this->redirect(['index']);
        }

        return $this->renderAjax('close', [
            'id' => $id
        ]);
    }

    /**
     * Create kanban task from ticket
     *
     * @param integer $id ticket id
     *
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws HttpException
     */
    public function actionCreateTask($id)
    {
        if (!$this->module->kanbanModule) {
            throw new HttpException(501, 'Required "Kanban" module does not exits', 0);
        }
        $model = new CreateTaskForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $ticket = $this->findModel($id);

            if ($model->createTask($ticket)) {
                Yii::$app->session->addFlash('success', Yii::t(
                    'simialbi/ticket/ticket/notification',
                    'Linked task for ticket <b>{name}</b> created',
                    ['name' => $ticket->subject]
                ));
            } else {
                Yii::$app->session->addFlash('danger', Yii::t(
                    'simialbi/ticket/ticket/notification',
                    'Failed to create linked task for ticket <b>{name}</b>',
                    ['name' => $ticket->subject]
                ));
            }
            return $this->redirect(['index']);
        }

        /** @var \simialbi\yii2\kanban\Module $module */
        $module = Yii::$app->getModule($this->module->kanbanModule);

        /** @var \simialbi\yii2\kanban\models\Board[] $boards */
        $boards = $module::getUserBoards();

        $buckets = [];
        foreach ($boards as $board) {
            $buckets[$board->name] = $board->getBuckets()
                ->select(['name', 'id'])
                ->orderBy(['name' => SORT_ASC])
                ->indexBy('id')
                ->column();
        }

        return $this->renderAjax('create-task', [
            'model' => $model,
            'buckets' => $buckets
        ]);
    }
}
