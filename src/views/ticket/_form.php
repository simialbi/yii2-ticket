<?php

use kartik\select2\Select2;

/* @var $this \yii\web\View */
/* @var $form \yii\bootstrap4\ActiveForm */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $priorities array */
/* @var $users array */

$isAgent = Yii::$app->user->can('ticketAgent');
echo $form->errorSummary($model); ?>
<div class="form-row">
    <?= $form->field($model, 'topic_id', [
        'options' => [
            'class' => ['form-group', 'col-12', 'col-sm-6', $isAgent ? 'col-lg-4' : '']
        ]
    ])->widget(Select2::class, [
        'data' => $topics,
        'theme' => Select2::THEME_KRAJEE_BS4,
        'bsVersion' => 4,
        'pluginOptions' => [
            'allowClear' => false
        ]
    ]); ?>
    <?= $form->field($model, 'priority', [
        'options' => [
            'class' => ['form-group', 'col-12', 'col-sm-6', $isAgent ? 'col-lg-4' : '']
        ]
    ])->widget(Select2::class, [
        'data' => $priorities,
        'theme' => Select2::THEME_KRAJEE_BS4,
        'bsVersion' => 4,
        'pluginOptions' => [
            'allowClear' => false
        ]
    ]); ?>
    <?php if ($isAgent): ?>
        <?= $form->field($model, 'created_by', [
            'options' => [
                'class' => ['form-group', 'col-12', 'col-sm-6', $isAgent ? 'col-lg-4' : '']
            ]
        ])->widget(Select2::class, [
            'data' => $users,
            'theme' => Select2::THEME_KRAJEE_BS4,
            'bsVersion' => 4,
            'pluginOptions' => [
                'allowClear' => false
            ]
        ]); ?>
    <?php endif; ?>
</div>
<div class="form-row">
    <?= $form->field($model, 'subject', [
        'options' => [
            'class' => ['form-group', 'col-12']
        ]
    ])->textInput(); ?>
</div>
<div class="form-row">
    <?= $form->field($model, 'description', [
        'options' => [
            'class' => ['form-group', 'col-12']
        ]
    ])->textarea(['rows' => 5]); ?>
</div>
