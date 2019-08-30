<?php

use kartik\select2\Select2;
use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $users array */

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
        <?php $form = ActiveForm::begin([
            'id' => 'assignTicketForm'
        ]); ?>
        <div class="modal-header">
            <h5 class="modal-title"><?= Yii::t('simialbi/ticket', 'Assign ticket'); ?></h5>
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
            <?= $form->field($model, 'assigned_to')->widget(Select2::class, [
                'data' => $users,
                'theme' => Select2::THEME_KRAJEE_BS4,
                'pluginOptions' => [
                    'allowClear' => false
                ]
            ]) ?>
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
        <?php ActiveForm::end(); ?>
    </div>
<?php
Pjax::end();
