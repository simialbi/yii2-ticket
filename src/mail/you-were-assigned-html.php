<?php

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $users array */
/* @var $statuses array */
/* @var $priorities array */

?>
<div class="you-were-assigned-mail">
    <h1 style="margin-bottom: 0;">
        <?= Yii::t('simialbi/ticket/mail', 'Ticket notification for {id}', [
            'id' => $model->id
        ]); ?>
    </h1>

    <p>
        <?= Yii::t(
            'simialbi/ticket/mail',
            'You\'ve been assigned to the ticket <b>{subject}</b> by <b>{user}</b>',
            [
                'subject' => $model->subject,
                'user' => $model->referrer->name
            ]
        ); ?>
    </p>

    <?= $this->render('_ticket-info-html', [
        'model' => $model,
        'topics' => $topics,
        'users' => $users,
        'statuses' => $statuses,
        'priorities' => $priorities
    ]); ?>
</div>
