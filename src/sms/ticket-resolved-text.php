<?php

/* @var $this \yii\web\View */
/* @var $model \simialbi\yii2\ticket\models\Ticket */
/* @var $topics array */
/* @var $users array */
/* @var $statuses array */
/* @var $priorities array */
/* @var $isRichText boolean */

echo Yii::t('simialbi/ticket/mail', 'Ticket notification for {id}', [
    'id' => $model->id
]);
?>

<?= strip_tags(Yii::t('simialbi/ticket/mail', 'Ticket <i>{id}</i> has been resolved', [
    'id' => $model->id
]));
