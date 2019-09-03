<?php

use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */

$this->title = $model->subject;
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('simialbi/ticket', 'My tickets'),
        'url' => ['ticket/index']
    ],
    $this->title
];

?>

<div class="sa-ticket-ticket-view">
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <figure class="img m-0 d-flex align-items-center">
                        <?php if ($model->author->image): ?>
                            <img src="<?= $model->author->image; ?>" alt="<?= Html::encode($model->author->name); ?>"
                                 class="rounded-circle" style="height: 50px; width: 50px;">
                        <?php endif; ?>
                        <figcaption class="meta ml-4">
                            <strong><?= Html::encode($model->author->name); ?></strong>
                            <br>
                            <time datetime="<?= Yii::$app->formatter->asDatetime(
                                $model->created_at,
                                'yyyy-MM-dd hh:mm'
                            ); ?>">
                                <?= Yii::$app->formatter->asRelativeTime($model->created_at); ?>
                            </time>
                        </figcaption>
                    </figure>
                </div>
                <div class="card-body">
                    <h4 class="card-title"><?= Html::encode($model->subject); ?></h4>
                    <?= str_replace(
                        '<p>',
                        '<p class="card-text">',
                        Yii::$app->formatter->asParagraphs($model->description)
                    ); ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 mt-3 mt-lg-0">
            <div class="card">
                <div class="card-header d-flex align-items-center text-gray">
                    <?= FAS::i('file', ['class' => 'mb-0', 'style' => ['font-size' => '20px', 'height' => '20px']]); ?>
                    <span class="h5 mb-0 ml-3"><?= Yii::t('simialbi/ticket', 'Attachments'); ?></span>
                </div>
                <?php if ($model->attachments): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($model->attachments as $attachment): ?>
                            <?= Html::a($attachment->name, ['attachment/view', 'id' => $attachment->id], [
                                'class' => ['list-group-item', 'list-group-item-action'],
                                'data' => [
                                    'toggle' => 'modal',
                                    'target' => '#ticketPreviewModal'
                                ]
                            ]); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card-body">
                        <?= Yii::t('yii', '(not set)'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($model->solution): ?>
        <div class="sa-ticket-solution card card-info text-white">
            <div class="card-header">
                <figure class="img m-0 d-flex align-items-center">
                    <?php if ($model->solution->author->image): ?>
                        <img src="<?= $model->solution->author->image; ?>" class="rounded-circle"
                             alt="<?= Html::encode($model->solution->author->name); ?>"
                             style="height: 50px; width: 50px;">
                    <?php endif; ?>
                    <figcaption class="meta ml-4">
                        <strong><?= Html::encode($model->agent->name); ?></strong>
                        <br>
                        <time datetime="<?= Yii::$app->formatter->asDatetime(
                            $model->solution->created_at,
                            'yyyy-MM-dd hh:mm'
                        ); ?>">
                            <?= Yii::$app->formatter->asRelativeTime($model->solution->created_at); ?>
                        </time>
                    </figcaption>
                </figure>
            </div>
            <div class="card-body">
                <?= str_replace(
                    '<p>',
                    '<p class="card-text">',
                    Yii::$app->formatter->asParagraphs($model->solution->text)
                ); ?>
            </div>
        </div>
    <?php endif; ?>

    <hr>

    <div class="row">
        <div class="col-12">
            <?= $this->render('/comment/ticket-comments', [
                'newComment' => Yii::createObject([
                    'class' => 'simialbi\yii2\ticket\models\Comment',
                    'ticket_id' => $model->id
                ]),
                'ticket' => $model
            ]); ?>
        </div>
    </div>
</div>
<?php
Modal::begin([
    'id' => 'ticketPreviewModal',
    'options' => [
        'class' => ['modal', 'remote', 'fade']
    ],
    'size' => Modal::SIZE_LARGE,
    'title' => null,
    'closeButton' => false
]);
Modal::end();

$this->registerJs("jQuery('#ticketPreviewModal').on('show.bs.modal', function (evt) {
    var link = jQuery(evt.relatedTarget);
    var href = link.prop('href');

    var modal = jQuery(this);
    modal.find('.modal-content').load(href);
});");
?>
