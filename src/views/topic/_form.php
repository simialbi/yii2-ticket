<?php

use kartik\select2\Select2;

/* @var $this \yii\web\View */
/* @var $form \yii\bootstrap4\ActiveForm */
/* @var $model \simialbi\yii2\ticket\models\Topic */
/* @var $users array */
/* @var $statuses array */

?>

<div class="form-row align-items-center">
    <?= $form->field($model, 'name', [
        'options' => [
            'class' => ['form-group', 'col-12', 'col-sm-9']
        ]
    ])->textInput(); ?>
    <?= $form->field($model, 'status', [
        'options' => [
            'class' => ['form-group', 'col-12', 'col-sm-3', 'col-md-2', 'offset-md-1']
        ]
    ])->checkbox(); ?>
</div>
<div class="form-row">
    <?= $form->field($model, 'new_ticket_assign_to', [
        'options' => [
            'class' => ['form-group', 'col-12', 'col-md-8', 'col-lg-6']
        ]
    ])->widget(Select2::class, [
        'data' => $users,
        'theme' => Select2::THEME_KRAJEE_BS4,
        'bsVersion' => 4,
        'options' => [
            'placeholder' => Yii::t('simialbi/ticket', 'Select user')
        ],
        'pluginOptions' => [
            'allowClear' => true
        ]
    ]); ?>
    <?= $form->field($model, 'new_ticket_status', [
        'options' => [
            'class' => ['form-group', 'col-12', 'col-md-4', 'col-lg-6']
        ]
    ])->widget(Select2::class, [
        'data' => $statuses,
        'theme' => Select2::THEME_KRAJEE_BS4,
        'bsVersion' => 4,
        'options' => [
            'placeholder' => ''
        ],
        'pluginOptions' => [
            'allowClear' => false
        ]
    ]); ?>
</div>
