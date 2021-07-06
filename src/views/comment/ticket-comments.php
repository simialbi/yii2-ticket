<?php

use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $ticket \simialbi\yii2\ticket\models\Ticket */
/* @var $newComment \simialbi\yii2\ticket\models\Comment */
/* @var $richTextFields boolean */

Pjax::begin([
    'id' => 'createCommentPjax',
    'formSelector' => '#createCommentForm',
    'enablePushState' => false,
    'clientOptions' => [
        'skipOuterContainers' => true
    ]
]);
?>
    <div class="sa-ticket-ticket-comments d-flex flex-column">
        <?php if (Yii::$app->user->can('updateTicket', ['ticket' => $ticket])): ?>
            <div class="sa-comment-create">
                <?= $this->render('_form', [
                    'ticket' => $ticket,
                    'model' => $newComment,
                    'richTextFields' => $richTextFields
                ]); ?>
            </div>
        <?php endif; ?>

        <?php $i = 0; ?>
        <?php foreach ($ticket->history as $when => $items): ?>
            <?php foreach ($items as $item): ?>
                <?php if (is_string($item)): ?>
                    <div class="alert alert-info m-2 text-center w-75 align-self-center"><?= $item; ?></div>
                <?php else: ?>
                    <?= $this->render('_comment', [
                        'model' => $item,
                        'index' => $i++,
                        'richTextFields' => $richTextFields
                    ]); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php
Pjax::end();
