<?php

use yii\bootstrap4\ActiveForm;
use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\Html;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $priorities array */
/* @var $users array */

$this->title = Yii::t('simialbi/ticket', 'Update ticket');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('simialbi/ticket', 'My tickets'),
        'url' => ['ticket/index']
    ],
    [
        'label' => $model->subject,
        'url' => ['ticket/view', 'id' => $model->id]
    ],
    $this->title
];
?>
<div class="sa-ticket-ticket-update">
    <?php $form = ActiveForm::begin([
        'id' => 'updateTicketForm'
    ]); ?>

    <?= $this->render('_form', [
        'form' => $form,
        'model' => $model,
        'topics' => $topics,
        'priorities' => $priorities,
        'users' => $users
    ]); ?>

    <div class="form-row">
        <div class="col-12 form-group d-flex align-items-center">
            <?= Html::submitButton(FAS::i('save') . ' ' . Yii::t('simialbi/ticket', 'Update ticket'), [
                'class' => ['btn', 'btn-primary', 'btn-sm']
            ]); ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
