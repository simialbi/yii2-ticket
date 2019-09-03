<?php

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $users array */
/* @var $statuses array */
/* @var $priorities array */

echo Yii::t('simialbi/ticket/mail', 'Ticket notification for {id}', [
    'id' => $model->id
]);
?>

<?= strip_tags(Yii::t(
    'simialbi/ticket/mail',
    'You\'ve been assigned to the ticket <b>{subject}</b> by <b>{user}</b>',
    [
        'subject' => $model->subject,
        'user' => $model->referrer->name
    ]
)); ?>

<?= $this->render('_ticket-info-text', [
    'model' => $model,
    'topics' => $topics,
    'users' => $users,
    'statuses' => $statuses,
    'priorities' => $priorities
]);
