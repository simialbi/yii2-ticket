<?php

use rmrevin\yii\fontawesome\CdnFreeAssetBundle;
use rmrevin\yii\fontawesome\FAS;
use simialbi\yii2\widgets\CommentInput;
use yii\bootstrap4\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Comment */

$form = ActiveForm::begin([
    'id' => 'createCommentForm',
    'action' => ['comment/create', 'ticketId' => $model->ticket_id]
]);

CdnFreeAssetBundle::register($this);

echo $form->field($model, 'ticket_id', ['options' => ['class' => ['m-0']]])->hiddenInput()->label(false);
?>
    <div class="form-row">
        <?= $form->field($model, 'text', [
            'labelOptions' => [
                'class' => ['sr-only']
            ],
            'options' => ['class' => ['form-group', 'col-12']]
        ])->widget(CommentInput::class, [
            'image' => ArrayHelper::getValue(Yii::$app->user->identity, 'image'),
            'imageOptions' => [
                'class' => ['mr-3', 'rounded-circle'],
                'style' => [
                    'height' => '40px',
                    'object-fit' => 'cover',
                    'object-position' => 'center',
                    'width' => '40px'
                ]
            ],
            'buttonOptions' => [
                'icon' => FAS::i('paper-plane'),
                'class' => ['btn', 'btn-primary']
            ],
            'options' => [
                'class' => ['form-control'],
                'placeholder' => $model->getAttributeLabel('text'),
                'rows' => 1
            ]
        ]); ?>
    </div>
<?php
ActiveForm::end();

