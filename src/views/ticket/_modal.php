<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;

Modal::begin([
    'id' => 'ticketModal',
    'options' => [
        'class' => ['modal', 'remote', 'fade'],
        'tabindex' => ''
    ],
    'clientOptions' => [
        'backdrop' => 'static',
        'keyboard' => false
    ],
    'size' => Modal::SIZE_LARGE,
    'title' => null,
    'closeButton' => false
]);
Modal::end();

$this->registerJs("jQuery('#ticketModal').on('show.bs.modal', function (evt) {
    var link = jQuery(evt.relatedTarget);
    var href = link.prop('href');

    var modal = jQuery(this);
    modal.find('.modal-content').load(href);
});");
