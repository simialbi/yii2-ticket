<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\ticket\models\Comment;
use simialbi\yii2\ticket\models\Ticket;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

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
