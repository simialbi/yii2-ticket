<?php

use rmrevin\yii\fontawesome\FAS;
use simialbi\yii2\ticket\models\Ticket;
use simialbi\yii2\widgets\CommentInput;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

/** @var $this \yii\web\View */
/** @var $ticket \simialbi\yii2\ticket\models\Ticket */
/** @var $model \simialbi\yii2\ticket\models\Comment */
/** @var $richTextFields boolean */

$form = ActiveForm::begin([
    'id' => 'createCommentForm',
    'action' => ['comment/create', 'ticketId' => $model->ticket_id]
]);

$icon = FAS::i('paperclip');
$close = '';
if (Yii::$app->user->can('closeTicket', ['ticket' => $ticket])) {
    $close = Html::beginTag('div', ['class' => 'input-group-prepend']);
    $close .= $form->field($ticket, 'status', [
        'options' => [
            'class' => 'input-group-text'
        ]
    ])->checkbox([
        'value' => Ticket::STATUS_RESOLVED,
        'uncheck' => Ticket::STATUS_IN_PROGRESS,
        'label' => Html::label(
            Yii::t('simialbi/ticket', 'Resolve'),
            Html::getInputId($ticket, 'status'),
            [
                'class' => 'custom-control-label'
            ]
        )
    ]);
    $close .= Html::endTag('div');
} else {
    $close = $form->field($ticket, 'status', [
        'options' => [
            'class' => ''
        ]
    ])->hiddenInput(['value' => Ticket::STATUS_IN_PROGRESS])->label(false);
}
$template = <<<HTML
{beginWrapper}
    {image}
    $close
    {input}
    <div class="input-group-append">
        <a href="javascript:;" id="file-upload" class="btn btn-secondary">$icon</a>
    </div>
    {submit}
{endWrapper}
HTML;


echo $form->field($model, 'ticket_id', ['options' => ['class' => ['m-0']]])->hiddenInput()->label(false);
?>
    <div class="form-row" id="file-placeholder"></div>
    <div class="form-row">
        <?= $form->field($model, 'text', [
            'labelOptions' => [
                'class' => ['sr-only']
            ],
            'options' => ['class' => ['form-group', 'col-12']]
        ])->widget(CommentInput::class, [
            'template' => $template,
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
            ],
            'richTextField' => $richTextFields,
            'summernoteClientOptions' => [
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
                'height' => 100,
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
    </div>

<?= $this->render('/attachment/_resumable', [
    'filePlaceholder' => 'file-placeholder',
    'browseButton' => 'file-upload'
]); ?>
<?php
ActiveForm::end();

