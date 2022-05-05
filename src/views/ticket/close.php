<?php

/** @var int $id Ticketid */

use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\Html;
use yii\widgets\Pjax;

Pjax::begin([
    'id' => 'assignTicketPjax',
    'formSelector' => '#assignTicketForm',
    'enablePushState' => false,
    'clientOptions' => [
        'skipOuterContainers' => true
    ]
]);
?>
    <div class="sa-assign-ticket-modal">
        <?= Html::beginForm(); ?>
        <div class="modal-header">
            <h5 class="modal-title"><?= Yii::t('simialbi/ticket', 'Close ticket'); ?></h5>
            <?= Html::button('<span aria-hidden="true">' . FAS::i('times') . '</span>', [
                'type' => 'button',
                'class' => ['close'],
                'data' => [
                    'dismiss' => 'modal'
                ],
                'aria' => [
                    'label' => Yii::t('simialbi/ticket', 'Close')
                ]
            ]); ?>
        </div>
        <div class="modal-body">
            <label for=""><?= Yii::t('simialbi/ticket', 'Solution') ?></label>
            <?= Html::textarea('comment', '', [
                'class' => [
                    'form-control'
                ]
            ]); ?>
        </div>
        <div class="modal-footer">
            <?= Html::button(Yii::t('simialbi/ticket', 'Close'), [
                'type' => 'button',
                'class' => ['btn', 'btn-dark'],
                'data' => [
                    'dismiss' => 'modal'
                ],
                'aria' => [
                    'label' => Yii::t('simialbi/ticket', 'Close')
                ]
            ]); ?>
            <?= Html::submitButton(Yii::t('simialbi/ticket', 'Save'), [
                'type' => 'button',
                'class' => ['btn', 'btn-success'],
                'aria' => [
                    'label' => Yii::t('simialbi/ticket', 'Save')
                ]
            ]); ?>
        </div>
        <?= Html::endForm(); ?>
    </div>
<?php
Pjax::end();
