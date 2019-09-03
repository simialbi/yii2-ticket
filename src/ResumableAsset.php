<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket;

use simialbi\yii2\web\AssetBundle;

class ResumableAsset extends AssetBundle
{
    /**
     * {@inheritDoc}
     */
    public $sourcePath = '@bower';

    /**
     * {@inheritDoc}
     */
    public $js = [
        'resumablejs/resumable.js'
    ];
}
