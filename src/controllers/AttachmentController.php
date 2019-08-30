<?php
/**
 * @package yii2-ticket
 * @author Simon Karlen <simi.albi@outlook.com>
 * @copyright Copyright © 2019 Simon Karlen
 */

namespace simialbi\yii2\ticket\controllers;

use simialbi\yii2\ticket\models\Attachment;
use Yii;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AttachmentController extends Controller
{
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
