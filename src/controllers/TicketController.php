<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\ticket\models\Attachment;
use simialbi\yii2\ticket\models\SearchTicket;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\models\Topic;
use simialbi\yii2\ticket\Module;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

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
        $searchModel = new SearchTicket();
        $userId = null;
        $defaultFilter = [];
        if (!Yii::$app->user->can('ticketAgent')) {
            $userId = Yii::$app->user->id;
        } else {
            $defaultFilter = [
                'SearchTicket' => [
                    'or',
                    ['assigned_to' => (string)Yii::$app->user->id],
                    ['created_by' => (string)Yii::$app->user->id]
                ]
            ];
        }
        $dataProvider = $searchModel->search(
            ArrayHelper::merge($defaultFilter, Yii::$app->request->queryParams),
            $userId
        );

        $topics = Topic::find()->select(['name', 'id'])->orderBy(['name' => SORT_ASC])->indexBy('id')->column();
        $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'topics' => $topics,
            'users' => $users,
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
            'model' => $model
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

            if ($this->module->sendMails && Yii::$app->mailer) {
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
                $recipients = [];
                foreach ($model->topic->agents as $agent) {
                    $recipients[$agent->email] = $agent->name;
                }

                Yii::$app->mailer->compose([
                    'html' => '@simialbi/yii2/ticket/mail/new-ticket-html',
                    'text' => '@simialbi/yii2/ticket/mail/new-ticket-text'
                ], [
                    'model' => $model,
                    'topics' => $topics,
                    'users' => $users,
                    'statuses' => Module::getStatuses(),
                    'priorities' => Module::getPriorities()
                ])
                    ->setFrom($from)
                    ->setTo($recipients)
                    ->setSubject(Yii::t('simialbi/ticket/mail', 'New Ticket: {id} {subject}', [
                        'id' => $model->id,
                        'subject' => $model->subject
                    ]))
                    ->send();
            }

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
            if ($this->module->sendMails && Yii::$app->mailer) {
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
                    'html' => '@simialbi/yii2/ticket/mail/you-were-assigned-html',
                    'text' => '@simialbi/yii2/ticket/mail/you-were-assigned-text'
                ], [
                    'model' => $model,
                    'topics' => $topics,
                    'users' => $users,
                    'statuses' => Module::getStatuses(),
                    'priorities' => Module::getPriorities()
                ])
                    ->setFrom($from)
                    ->setTo([$model->agent->email => $model->agent->name])
                    ->setSubject(Yii::t('simialbi/ticket/mail', 'You\'ve been assigned to a ticket: {id} {subject}', [
                        'id' => $model->id,
                        'subject' => $model->subject
                    ]))
                    ->send();
            }
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
        $model->save();

        return $this->redirect(['index']);
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
}
