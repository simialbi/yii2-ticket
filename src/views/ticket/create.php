<?php

use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */

?>
<div class="sa-ticket-ticket-create">
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'topic_id', [
        'options' => [
            'class' => ['form-group']
        ]
    ])->widget(Select2::class, [
        'bsVersion' => 4,
        'pluginOptions' => [
            'allowClear' => false
        ]
    ]); ?>
    <?php ActiveForm::end(); ?>
</div>
