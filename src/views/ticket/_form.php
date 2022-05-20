<?php

use kartik\select2\Select2;
use marqu3s\summernote\Summernote;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

/** @var $this \yii\web\View */
/** @var $form \yii\bootstrap4\ActiveForm */
/** @var $model \simialbi\yii2\ticket\models\Ticket */
/** @var $topics \simialbi\yii2\ticket\models\Topic[] */
/** @var $priorities array */
/** @var $users array */
/** @var $richTextFields boolean */

$isAgent = Yii::$app->user->can('ticketAgent');
echo $form->errorSummary($model); ?>
<div class="form-row">
    <?= $form->field($model, 'topic_id', [
        'options' => [
            'class' => ['form-group', 'col-12', 'col-sm-6', $isAgent ? 'col-lg-4' : '']
        ]
    ])->widget(Select2::class, [
        'data' => ArrayHelper::map($topics, 'id', 'name'),
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
    <?php if ($richTextFields): ?>
        <?= $form->field($model, 'description', [
            'options' => [
                'class' => ['form-group', 'col-12']
            ]
        ])->widget(Summernote::class, [
            'clientOptions' => [
                'callbacks' => [
                    'onPaste' => new JsExpression('function (e) {
                        var files = ((e.originalEvent || e).clipboardData || window.clipboardData).files;
                        if (files && files.length && resumable) {
                            resumable.addFiles(files, e);
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            return false;
                        }
                    }')
                ],
                'disableDragAndDrop' => true,
                'styleTags' => [
                    'p',
                    [
                        'title' => 'blockquote',
                        'tag' => 'blockquote',
                        'className' => 'blockquote',
                        'value' => 'blockquote'
                    ],
                    'pre'
                ],
                'toolbar' => [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                    ['script', ['subscript', 'superscript']],
                    ['list', ['ol', 'ul']],
                    ['clear', ['clear']]
                ]
            ]
        ]); ?>
    <?php else: ?>
        <?= $form->field($model, 'description', [
            'options' => [
                'class' => ['form-group', 'col-12']
            ]
        ])->textarea(['rows' => 5]); ?>
    <?php endif; ?>
</div>
