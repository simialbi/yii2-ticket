<?php

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $users array */
/* @var $statuses array */
/* @var $priorities array */

echo Yii::t('simialbi/ticket/mail', 'Ticket notification for {id}', [
    'id' => $model->id
]);
?>

<?= strip_tags(Yii::t('simialbi/ticket/mail', 'There is a new ticket <i>{id}</i>', [
    'id' => $model->id
])); ?>

<?= strip_tags(preg_replace('#</p><p>|<br ?/?>#g', "\n", $model->description));
