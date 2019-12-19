<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\kanban\models\TaskUserAssignment;
use simialbi\yii2\ticket\behaviors\SendMailBehavior;
use simialbi\yii2\ticket\models\Attachment;
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
                        'roles' => ['updateTicket'],
                        'roleParams' => function () {
                            return ['ticket' => $this->findModel(Yii::$app->request->get('id'))];
                        }
                    ]
                ]
            ]
        ];
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
        } elseif (!Yii::$app->user->can('assignTicket')) {
            $searchModel->assigned_to = (string)Yii::$app->user->id;
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
            'hasKanban' => (boolean)$this->module->kanbanModule
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
            'priority' => Ticket::PRIORITY_NORMAL
        ]);
        if ($this->module->sendMails) {
            $model->attachBehavior('sendMail', [
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->topic->new_ticket_assign_to) {
                $model->assigned_to = $model->topic->new_ticket_assign_to;
            }
            if ($model->topic->new_ticket_status) {
                $model->status = $model->topic->new_ticket_status;
            }
            if (!empty($model->dirtyAttributes)) {
                $model->save();
            }
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

        $topics = Topic::find()->select([
            'name',
            'id'
        ])->orderBy([
            'name' => SORT_ASC
        ])->indexBy('id')->column();
        $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');

        return $this->render('create', [
            'model' => $model,
            'topics' => $topics,
            'users' => $users,
            'priorities' => Module::getPriorities()
        ]);
    }

    /**
     * @param integer $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAssign($id)
    {
        $model = $this->findModel($id);
        $model->scenario = $model::SCENARIO_ASSIGN;

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

            return $this->redirect(['index']);
        }

        $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');

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

        return $this->redirect(['index']);
    }

    /**
     * Close a ticket
     *
     * @param integer $id
     *
     * @return \yii\web\Response
     *
     * @throws NotFoundHttpException
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        $model->status = Ticket::STATUS_RESOLVED;

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

    /**
     * Create kanban task from ticket
     *
     * @param integer $id ticket id
     *
     * @return string
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
            if ($this->module->sendMails) {
                $model->attachBehavior('sendMail', [
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

            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
    }
}
