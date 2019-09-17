<?php

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $comment \simialbi\yii2\ticket\models\Comment */
/* @var $topics array */
/* @var $users array */
/* @var $statuses array */
/* @var $priorities array */

echo Yii::t('simialbi/ticket/mail', 'Ticket notification for {id}', [
    'id' => $model->id
]);
?>

<?= strip_tags(Yii::t('simialbi/ticket/mail', 'There is a new comment in ticket <i>{id}</i>', [
    'id' => $model->id
])); ?>

<?= $comment->text; ?>

<?= $this->render('_ticket-info-text', [
    'model' => $model,
    'topics' => $topics,
    'users' => $users,
    'statuses' => $statuses,
    'priorities' => $priorities
]);
