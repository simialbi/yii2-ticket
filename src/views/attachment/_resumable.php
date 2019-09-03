<?php

use simialbi\yii2\ticket\ResumableAsset;
use yii\helpers\Json;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $filePlaceholder string */
/* @var $browseButton string */

ResumableAsset::register($this);

$url = Url::to(['attachment/upload']);
$fileExistUrl = Url::to(['attachment/exists']);
$deleteUrl = Url::to(['attachment/delete']);

$params = [];
if (Yii::$app->request->enableCsrfValidation) {
    $params[Yii::$app->request->csrfParam] = Yii::$app->request->getCsrfToken();
}
$params = Json::htmlEncode($params);

$template = <<<HTML
<div class="col-12 d-flex align-items-center justify-content-stretch mb-2 bg-light px-3 py-2" id="file-{identifier}">
    <span class="file-name flex-grow-0">{name}</span>
    <div class="progress flex-grow-1 mx-2">
        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <a href="javascript:;" class="delete-link flex-grow-0 text-dark d-none">
        &times;
    </a>
</div>
HTML;

$template = preg_replace('#[\r\n\t]#', '', $template);

$js = <<<JS
var resumable = new Resumable({
    target: '$url',
    testTarget: '$fileExistUrl',
    query: $params,
    chunkNumberParameterName: false,
    totalChunksParameterName: false,
    chunkSizeParameterName: false,
    totalSizeParameterName: 'fileSize',
    identifierParameterName: 'identifier',
    fileNameParameterName: 'fileName',
    relativePathParameterName: false,
    currentChunkSizeParameterName: false,
    typeParameterName: 'fileType'
});

resumable.assignBrowse(document.getElementById('$browseButton'));

resumable.on('fileAdded', function (file) {
    resumable.upload();
    var container = jQuery('#$filePlaceholder');
    var el = '$template';
    
    el = el.replace('{identifier}', file.uniqueIdentifier);
    el = el.replace('{name}', file.fileName);
    
    container.append(el);
});
resumable.on('fileProgress', function (file) {
    var el = jQuery('#file-' + file.uniqueIdentifier),
        progress = file.progress() * 100;
    el.find('.progress-bar').attr('aria-valuenow', progress).css('width', progress + '%');
});
resumable.on('fileSuccess', function (file, msg) {
    var el = jQuery('#file-' + file.uniqueIdentifier),
        uFile = JSON.parse(msg);
    el.find('.file-name').replaceWith('<a class="file-name flex-grow-0" href="' + uFile.path + '" target="_blank">' + file.fileName + '</a>');
    el.prepend('<input type="hidden" name="attachments[]" value="' + file.uniqueIdentifier + '">');
    el.find('.delete-link').removeClass('d-none').on('click', function () {
        jQuery.ajax({
            url: '$deleteUrl?identifier=' + file.uniqueIdentifier,
            method: 'DELETE'
        }).done(function () {
            el.remove();
        });
    });
});
JS;

$this->registerJs($js);

?>
