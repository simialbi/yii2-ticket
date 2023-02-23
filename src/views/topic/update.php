<?php

use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/** @var $this \yii\web\View */
/** @var $model \simialbi\yii2\ticket\models\Topic */
/** @var $agents array */
/** @var $users array */
/** @var $statuses array */
/** @var $richTextFields boolean */
/** @var $canAssignTicketsToNonAgents boolean */
/** @var $notifications \simialbi\yii2\ticket\models\TopicNotification[] */

$this->title = Yii::t('simialbi/ticket/topic', 'Update topic');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('simialbi/ticket/topic', 'Topics'),
        'url' => ['topic/index']
    ],
    $this->title
];

?>
<div class="sa-ticket-topic-update">
    <?php $form = ActiveForm::begin([
        'id' => 'updateTopicForm'
    ]); ?>

    <?= $this->render('_form', [
        'form' => $form,
        'model' => $model,
        'statuses' => $statuses,
        'agents' => $agents,
        'users' => $users,
        'richTextFields' => $richTextFields,
        'canAssignTicketsToNonAgents' => $canAssignTicketsToNonAgents,
        'notifications' => $notifications
    ]); ?>

    <div class="form-row">
        <div class="col-12 form-group d-flex align-items-center">
            <?= Html::submitButton(FAS::i('save') . ' ' . Yii::t('simialbi/ticket/topic', 'Update topic'), [
                'class' => ['btn', 'btn-primary', 'btn-sm']
            ]); ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
