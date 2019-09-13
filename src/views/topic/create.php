<?php

use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Topic */
/* @var $users array */
/* @var $statuses array */

$this->title = Yii::t('simialbi/ticket/topic', 'Create topic');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('simialbi/ticket/topic', 'Topics'),
        'url' => ['topic/index']
    ],
    $this->title
];

?>
<div class="sa-ticket-topic-create">
    <?php $form = ActiveForm::begin([
        'id' => 'createTopicForm'
    ]); ?>

    <?= $this->render('_form', [
        'form' => $form,
        'model' => $model,
        'statuses' => $statuses,
        'users' => $users
    ]); ?>

    <div class="form-row">
        <div class="col-12 form-group d-flex align-items-center">
            <?= Html::submitButton(FAS::i('save') . ' ' . Yii::t('simialbi/ticket/topic', 'Create topic'), [
                'class' => ['btn', 'btn-primary', 'btn-sm']
            ]); ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
