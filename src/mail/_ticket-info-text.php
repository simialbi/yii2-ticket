<?php

use yii\console\widgets\Table;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $users array */
/* @var $statuses array */
/* @var $priorities array */

echo Table::widget([
    'rows' => [
        [$model->getAttributeLabel('id'), $model->id],
        [$model->getAttributeLabel('created_at'), Yii::$app->formatter->asDatetime($model->created_at)],
        [$model->getAttributeLabel('subject'), $model->subject],
        [$model->getAttributeLabel('description'), $model->description],
        [
            $model->getAttributeLabel('created_by'),
            ArrayHelper::getValue($users, $model->created_by, $model->created_by)
        ],
        [
            $model->getAttributeLabel('topic_id'),
            ArrayHelper::getValue($topics, $model->topic_id, $model->topic_id)
        ],
        [
            $model->getAttributeLabel('priority'),
            ArrayHelper::getValue($priorities, $model->priority, $model->priority)
        ],
        [$model->getAttributeLabel('status'), ArrayHelper::getValue($statuses, $model->status, $model->status)],
    ],
    'screenWidth' => 1920
]);
echo "\r\n\r\n";
echo strip_tags(Yii::t('simialbi/ticket/mail', 'Link to ticket <b>{id}</b>', ['id' => $model->id])) . "\n";
echo ' ' . Url::to(['ticket/view', 'id' => $model->id], Yii::$app->request->isSecureConnection ? 'https' : 'http');
