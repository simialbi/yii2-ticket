<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\ticket\models\SearchTopic;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\models\Topic;
use simialbi\yii2\ticket\models\TopicNotification;
use simialbi\yii2\ticket\Module;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class TopicController
 * @package simialbi\yii2\ticket\controllers
 *
 * @property-read Module $module
 */
class TopicController extends Controller
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
                        'roles' => ['changeTicketSettings']
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
        $searchModel = new SearchTopic();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'users' => $users,
            'statuses' => Module::getStatuses(),
            'priorities' => Module::getPriorities()
        ]);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        $model = new Topic(['new_ticket_status' => Ticket::STATUS_OPEN, 'status' => true]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $agents = Yii::$app->request->getBodyParam('agents', []);
            $rows = array_map(function ($item) use ($model) {
                return [
                    $model->id,
                    $item
                ];
            }, $agents);
            $model::getDb()->createCommand()->batchInsert('{{%ticket__topic_agent}}', [
                'topic_id',
                'agent_id'
            ], $rows)->execute();

            $this->saveTopicNotifications($model);

            return $this->redirect(['index']);
        }

        $agents = $this->module->getAgents();

        $users = ArrayHelper::map($model->agents, 'id', 'name');
        if ($this->module->canAssignTicketsToNonAgents) {
            $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');
        }

        return $this->render('create', [
            'model' => $model,
            'agents' => $agents,
            'users' => $users,
            'statuses' => Module::getStatuses(),
            'richTextFields' => $this->module->richTextFields,
            'canAssignTicketsToNonAgents' => $this->module->canAssignTicketsToNonAgents,
            'selection' => []
        ]);
    }

    /**
     * @param integer $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $agents = Yii::$app->request->getBodyParam('agents', []);
            $rows = array_map(function ($item) use ($model) {
                return [
                    $model->id,
                    $item
                ];
            }, $agents);

            $model::getDb()->createCommand()->delete('{{%ticket__topic_agent}}', [
                'topic_id' => $model->id
            ])->execute();
            $model::getDb()->createCommand()->batchInsert('{{%ticket__topic_agent}}', [
                'topic_id',
                'agent_id'
            ], $rows)->execute();

            $this->saveTopicNotifications($model);

            return $this->redirect(['index']);
        }

        $agents = $this->module->getAgents();

        $users = ArrayHelper::map($model->agents, 'id', 'name');
        if ($this->module->canAssignTicketsToNonAgents) {
            $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');
        }

        $topicNotifications = TopicNotification::find()
            ->select(['medium', 'event'])
            ->where([
                'topic_id' => $model->id
            ])
            //->indexBy('event')
            ->asArray()
            ->all();

        $selection = ArrayHelper::map($topicNotifications, 'medium', 'medium', 'event');

        return $this->render('update', [
            'model' => $model,
            'agents' => $agents,
            'users' => $users,
            'statuses' => Module::getStatuses(),
            'richTextFields' => $this->module->richTextFields,
            'canAssignTicketsToNonAgents' => $this->module->canAssignTicketsToNonAgents,
            'selection' => $selection
        ]);
    }

    /**
     * @param integer $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     *
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t(
                'simialbi/ticket/notification',
                'Topic <b>{topic}</b> deleted',
                [
                    'topic' => $model->name
                ]
            ));
        } else {
            foreach ($model->errors as $errors) {
                foreach ($errors as $error) {
                    Yii::$app->session->addFlash('danger', $error);
                }
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Save the notification settings
     * @param Topic $model
     * @return void
     */
    protected function saveTopicNotifications($model)
    {
        TopicNotification::deleteAll([
            'topic_id' => $model->id
        ]);
        foreach (Topic::getEvents() as $event) {
            $mediums = Yii::$app->request->post($event);
            if (!$mediums) {
                continue;
            }
            foreach ($mediums as $medium) {
                $topicNot = new TopicNotification([
                    'topic_id' => $model->id,
                    'event' => $event,
                    'medium' => $medium
                ]);
                $topicNot->save();
            }
        }
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Topic the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Topic::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
    }
}
