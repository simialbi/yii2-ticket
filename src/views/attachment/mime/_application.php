<?php

use yii\bootstrap4\Html;

/** @var $this \yii\web\View */
/** @var $model \simialbi\yii2\ticket\models\Attachment */

echo Html::tag('iframe', '', [
    'src' => $model->path,
    'class' => ['d-block', 'border-0', 'w-100'],
    'style' => [
        'height' => '60vh'
    ]
]);
