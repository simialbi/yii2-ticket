<?php

use kartik\select2\Select2;
use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\CreateTaskForm */
/* @var $buckets array */

Pjax::begin([
    'id' => 'createTaskPjax',
    'formSelector' => '#assignTicketForm',
    'enablePushState' => false,
    'clientOptions' => [
        'skipOuterContainers' => true
    ]
]); ?>
    <div class="sa-crate-task-modal">
        <?php $form = ActiveForm::begin([
            'id' => 'assignTicketForm'
        ]); ?>
        <div class="modal-header">
            <h5 class="modal-title"><?= Yii::t('simialbi/ticket', 'Create Kanban task'); ?></h5>
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
            <div class="form-row">
                <?= $form->field($model, 'bucket_id', [
                    'options' => [
                        'class' => ['form-group', 'col-12']
                    ]
                ])->widget(Select2::class, [
                    'data' => $buckets,
                    'theme' => Select2::THEME_KRAJEE_BS4,
                    'pluginOptions' => [
                        'allowClear' => false
                    ]
                ]); ?>
            </div>
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
