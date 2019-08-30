<?php

use yii\bootstrap4\Html;

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Attachment */

echo Html::img($model->path, ['class' => ['img-fluid', 'd-block', 'mx-auto']]);
