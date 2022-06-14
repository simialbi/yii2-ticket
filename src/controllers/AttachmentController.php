<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\ticket\models\Attachment;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

class AttachmentController extends Controller
{
    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'exists' => ['GET'],
                    'upload' => ['POST', 'PUT'],
                    'delete' => ['DELETE']
                ]
            ]
        ];
    }

    /**
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $template = 'application';
        if (file_exists($model->localPath)) {
            list($template,) = explode('/', FileHelper::getMimeType($model->localPath));
        }

        if (!file_exists(Yii::getAlias('@simialbi/yii2/ticket/views/attachment/mime/_' . $template . '.php'))) {
            $template = 'application';
        }

        return $this->renderAjax('view', [
            'model' => $model,
            'template' => '_' . $template . '.php'
        ]);
    }

    /**
     * @param string $identifier
     * @return Attachment|null
     * @throws NotFoundHttpException
     */
    public function actionExists($identifier)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (($model = Attachment::findOne(['unique_id' => $identifier])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }

    /**
     * @param string $fileName
     * @param integer $fileSize
     * @param string $fileType
     * @param string $identifier
     * @param int $chunkNumber
     * @param int|null $chunkSize
     * @param int $totalChunks
     * @return Attachment|void
     * @throws ServerErrorHttpException
     * @throws \yii\base\Exception
     */
    public function actionUpload($fileName, $fileSize, $fileType, $identifier, $chunkNumber = 1, $totalChunks = 1)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $file = UploadedFile::getInstanceByName('file');

        $path = Yii::getAlias('@webroot/uploads');
        FileHelper::createDirectory($path);

        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;
        if ($chunkNumber === 1 && file_exists($filePath)) {
            $i = 1;
            do {
                $filePath = $path . DIRECTORY_SEPARATOR . $file->baseName . '_' . $i . '.' . $file->extension;
            } while (file_exists($filePath));
        }
        $filePath = FileHelper::normalizePath($filePath);
        if (false === file_put_contents($filePath, file_get_contents($file->tempName), FILE_APPEND)) {
            throw new ServerErrorHttpException();
        }
        if ($chunkNumber === $totalChunks) {
            $attachment = new Attachment([
                'unique_id' => $identifier,
                'name' => $fileName,
                'mime_type' => $fileType,
                'size' => $fileSize,
                'path' => Yii::getAlias('@web/uploads/' . $fileName)
            ]);
            $attachment->save();

            return $attachment;
        }

        Yii::$app->response->setStatusCode(201);
    }

    /**
     * @param string $identifier
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($identifier)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (($model = Attachment::findOne(['unique_id' => $identifier])) === null) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }

        $model->delete();
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     *
     * @return Attachment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Attachment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
    }
}
