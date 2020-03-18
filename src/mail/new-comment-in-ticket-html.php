<?php

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $comment \simialbi\yii2\ticket\models\Comment */
/* @var $topics array */
/* @var $users array */
/* @var $statuses array */
/* @var $priorities array */

?>
<div class="new-comment-in-ticket-mail">
    <h1 style="margin-bottom: 0;">
        <?= Yii::t('simialbi/ticket/mail', 'Ticket notification for {id}', [
            'id' => $model->id
        ]); ?>
    </h1>

    <p>
        <?= Yii::t('simialbi/ticket/mail', 'There is a new comment in ticket <i>{id}</i>', [
            'id' => $model->id
        ]); ?>
    </p>

    <?= Yii::$app->formatter->asNtext($comment->text); ?>

    <?= $this->render('_ticket-info-html', [
        'model' => $model,
        'topics' => $topics,
        'users' => $users,
        'statuses' => $statuses,
        'priorities' => $priorities
    ]); ?>
</div>
