<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $users array */
/* @var $statuses array */
/* @var $priorities array */
/* @var $isRichText boolean */

?>

<?= DetailView::widget([
    'model' => $model,
    'options' => [],
    'attributes' => [
        'id',
        'created_at:datetime',
        'subject',
        'description:' . ($isRichText ? 'html' : 'ntext'),
        [
            'attribute' => 'created_by',
            'value' => function ($model) use ($users) {
                /* @var $model \simialbi\yii2\ticket\models\Ticket */
                return ArrayHelper::getValue($users, $model->created_by, $model->created_by);
            }
        ],
        [
            'attribute' => 'topic_id',
            'value' => function ($model) use ($topics) {
                /* @var $model \simialbi\yii2\ticket\models\Ticket */
                return ArrayHelper::getValue($topics, $model->topic_id, $model->topic_id);
            }
        ],
        [
            'attribute' => 'priority',
            'value' => function ($model) use ($priorities) {
                /* @var $model \simialbi\yii2\ticket\models\Ticket */
                return ArrayHelper::getValue($priorities, $model->priority, $model->priority);
            }
        ],
        [
            'attribute' => 'status',
            'value' => function ($model) use ($statuses) {
                /* @var $model \simialbi\yii2\ticket\models\Ticket */
                return ArrayHelper::getValue($statuses, $model->status, $model->status);
            }
        ]
    ]
]); ?>

<p>
    <?= Html::a(
        Yii::t('simialbi/ticket/mail', 'Link to ticket <b>{id}</b>', ['id' => $model->id]),
        Url::to(['ticket/view', 'id' => $model->id], Yii::$app->request->isSecureConnection ? 'https' : 'http')
    ); ?>
</p>
