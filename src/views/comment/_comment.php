<?php

use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\Html;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Comment */
/* @var $index integer */
/* @var $richTextFields boolean */

?>

<div
    class="sa-ticket-comment media p-2 m-2 rounded position-relative <?= ($index % 2 === 0) ? 'bg-light' : 'bg-white'; ?>">
    <?php if ($model->author->image): ?>
        <img src="<?= $model->author->image; ?>" alt="<?= Html::encode($model->author->name); ?>"
             class="rounded-circle mr-3" style="height: 50px; width: 50px;">
    <?php endif; ?>
    <div class="media-body">
        <strong><?= Html::encode($model->author->name); ?></strong>
        <time datetime="<?= Yii::$app->formatter->asDatetime($model->created_at, 'yyyy-MM-dd HH:mm'); ?>"
              class="text-muted small">
            <?= Yii::$app->formatter->asRelativeTime($model->created_at); ?>
        </time>

        <p class="media-text mb-0">
            <?= ($richTextFields) ? $model->text : Yii::$app->formatter->asNtext($model->text); ?>
        </p>
        <?php if ($model->attachments): ?>
            <div class="sa-ticket-comment-attachments d-flex align-items-center">
                <?php foreach ($model->attachments as $attachment): ?>
                    <?= Html::a(
                        FAS::i($attachment->icon) . ' ' . $attachment->name,
                        ['attachment/view', 'id' => $attachment->id],
                        [
                            'class' => [
                                'sa-ticket-comment-attachment',
                                'mr-2',
                                'px-2',
                                'py-1',
                                'border',
                                'rounded-pill',
                                'text-reset'
                            ],
                            'data' => [
                                'toggle' => 'modal',
                                'target' => '#ticketPreviewModal'
                            ]
                        ]
                    ); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
