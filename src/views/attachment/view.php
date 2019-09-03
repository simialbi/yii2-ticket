<?php

use rmrevin\yii\fontawesome\FAS;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Attachment */
/* @var $template string */

?>

<div class="sa-assign-ticket-modal">
    <div class="modal-header">
        <h5 class="modal-title"><?= Html::encode($model->name); ?></h5>
        <?= Html::button('<span aria-hidden="true">' . FAS::i('times') . '</span>', [
            'type' => 'button',
            'class' => ['close'],
            'data' => [
                'dismiss' => 'modal'
            ],
            'aria' => [
                'label' => Yii::t('simialbi/ticket', 'Close')
            ]
        ]); ?>
    </div>
    <div class="modal-body">
        <?= $this->render('mime/' . $template, [
            'model' => $model
        ]); ?>
    </div>
</div>
