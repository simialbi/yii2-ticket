<?php

use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $priorities array */
/* @var $users array */
/* @var $richTextFields boolean */

$this->title = Yii::t('simialbi/ticket', 'Create ticket');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('simialbi/ticket', 'My tickets'),
        'url' => ['ticket/index']
    ],
    $this->title
];
?>
<div class="sa-ticket-ticket-create">
    <?php $form = ActiveForm::begin([
        'id' => 'createTicketForm'
    ]); ?>

    <?= $this->render('_form', [
        'form' => $form,
        'model' => $model,
        'topics' => $topics,
        'priorities' => $priorities,
        'users' => $users,
        'richTextFields' => $richTextFields
    ]); ?>

    <div class="form-row">
        <div class="col-12 form-group d-flex align-items-center">
            <?= Html::submitButton(FAS::i('save') . ' ' . Yii::t('simialbi/ticket', 'Create ticket'), [
                'class' => ['btn', 'btn-primary', 'btn-sm']
            ]); ?>
            <a href="javascript:;" id="file-upload" class="ml-3 btn btn-outline-secondary btn-sm">
                <?= FAS::i('paperclip'); ?>
            </a>
        </div>
    </div>
    <div class="form-row" id="file-placeholder"></div>
    <?php ActiveForm::end(); ?>
</div>

<?= $this->render('/attachment/_resumable', [
    'filePlaceholder' => 'file-placeholder',
    'browseButton' => 'file-upload'
]); ?>
