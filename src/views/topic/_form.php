<?php

use kartik\select2\Select2;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

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
            'class' => ['form-group', 'col-12', 'col-md-6', 'col-lg-4']
        ]
    ])->widget(Select2::class, [
        'data' => $users,
        'theme' => Select2::THEME_KRAJEE_BS4,
        'bsVersion' => 4,
        'options' => [
            'placeholder' => Yii::t('simialbi/ticket', 'Select user(s)')
        ],
        'pluginOptions' => [
            'allowClear' => true
        ]
    ]); ?>
    <?= $form->field($model, 'new_ticket_status', [
        'options' => [
            'class' => ['form-group', 'col-12', 'col-md-6', 'col-lg-4']
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
    <div class="form-group col-12 col-md-6 col-lg-4">
        <?= Html::label(Yii::t('simialbi/ticket', 'Responsible agents'), 'responsible-agents'); ?>
        <?= Select2::widget([
            'name' => 'agents',
            'value' => ArrayHelper::getColumn($model->getAgents(), 'id'),
            'id' => 'responsible-agents',
            'data' => $users,
            'options' => [
                'placeholder' => Yii::t('simialbi/ticket', 'Select user(s)'),
                'multiple' => true
            ],
            'pluginOptions' => [
                'allowClear' => true
            ]
        ]); ?>
    </div>
</div>
