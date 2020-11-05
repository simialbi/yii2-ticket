<?php

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $users array */
/* @var $statuses array */
/* @var $priorities array */
/* @var $isRichText boolean */

?>
<div class="new-ticket-mail">
    <h1 style="margin-bottom: 0;">
        <?= Yii::t('simialbi/ticket/mail', 'Ticket notification for {id}', [
            'id' => $model->id
        ]); ?>
    </h1>

    <p>
        <?= Yii::t('simialbi/ticket/mail', 'There is a new ticket <i>{id}</i>', [
            'id' => $model->id
        ]); ?>
    </p>

    <?= $this->render('_ticket-info-html', [
        'model' => $model,
        'topics' => $topics,
        'users' => $users,
        'statuses' => $statuses,
        'priorities' => $priorities,
        'isRichText' => $isRichText
    ]); ?>
</div>
