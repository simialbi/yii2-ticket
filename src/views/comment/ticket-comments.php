<?php

use rmrevin\yii\fontawesome\CdnFreeAssetBundle;
use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $ticket \simialbi\yii2\ticket\models\Ticket */
/* @var $newComment \simialbi\yii2\ticket\models\Comment */

CdnFreeAssetBundle::register($this);

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
                'model' => $newComment
            ]); ?>
        </div>

        <?php foreach ($ticket->comments as $comment): ?>
            <?= $this->render('_comment', [
                'model' => $comment
            ]); ?>
        <?php endforeach; ?>
    </div>
<?php
Pjax::end();
