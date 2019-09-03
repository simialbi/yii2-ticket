<?php

use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $ticket \simialbi\yii2\ticket\models\Ticket */
/* @var $newComment \simialbi\yii2\ticket\models\Comment */

Pjax::begin([
    'id' => 'createCommentPjax',
    'formSelector' => '#createCommentForm',
    'enablePushState' => false,
    'clientOptions' => [
        'skipOuterContainers' => true
    ]
]);
?>
    <div class="sa-ticket-ticket-comments">
        <div class="sa-comment-create">
            <?= $this->render('_form', [
                'ticket' => $ticket,
                'model' => $newComment
            ]); ?>
        </div>

        <?php $i = 0; ?>
        <?php foreach ($ticket->comments as $comment): ?>
            <?= $this->render('_comment', [
                'model' => $comment,
                'index' => $i++
            ]); ?>
        <?php endforeach; ?>
    </div>
<?php
Pjax::end();
