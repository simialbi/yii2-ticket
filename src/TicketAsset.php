<?php

namespace simialbi\yii2\ticket;

class TicketAsset extends \yii\web\AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = __DIR__ . '/assets/';

    /**
     * {@inheritDoc}
     */
    public $css = [
        'css/styles.css'
    ];

    /**
     * {@inheritdoc}
     */
    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}
