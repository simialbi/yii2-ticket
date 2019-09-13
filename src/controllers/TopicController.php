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
use simialbi\yii2\ticket\Module;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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
     */
    public function actionCreate()
    {
        $model = new Topic(['new_ticket_status' => Ticket::STATUS_OPEN, 'status' => true]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');

        return $this->render('create', [
            'model' => $model,
            'users' => $users,
            'statuses' => Module::getStatuses()
        ]);
    }

    /**
     * @param integer $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        $users = ArrayHelper::map(call_user_func([Yii::$app->user->identityClass, 'findIdentities']), 'id', 'name');

        return $this->render('update', [
            'model' => $model,
            'users' => $users,
            'statuses' => Module::getStatuses()
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
