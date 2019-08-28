<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\ticket\models\SearchTicket;
use simialbi\yii2\ticket\models\Ticket;
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

    public function actionCreate()
    {
        $model = new Ticket();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model
        ]);
    }
}
