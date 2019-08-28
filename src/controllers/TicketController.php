<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\ticket\models\SearchTicket;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\ticket\models\Topic;
use simialbi\yii2\ticket\Module;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Class TicketController
 * @package simialbi\yii2\ticket\controllers
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
        if (!Yii::$app->user->can('administrateTicket')) {
            $userId = Yii::$app->user->id;
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $userId);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Create a new ticket
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Ticket([
            'source_id' => 1,
            'priority' => Ticket::PRIORITY_NORMAL
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        $topics = Topic::find()->select([
            'name',
            'id'
        ])->orderBy([
            'name' => SORT_ASC
        ])->indexBy('id')->column();

        return $this->render('create', [
            'model' => $model,
            'topics' => $topics,
            'priorities' => Module::getPriorities()
        ]);
    }
}
