<?php

namespace simialbi\yii2\ticket\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "{{%ticket_attachment}}".
 *
 * @property integer $id
 * @property string $unique_id
 * @property integer $ticket_id
 * @property string $comment_id
 * @property string $name
 * @property string $path
 * @property string $mime_type
 * @property integer $size
 * @property string $created_by
 * @property string $updated_by
 * @property integer|string $created_at
 * @property integer|string $updated_at
 *
 * @property-read string $icon
 * @property-read string $localPath
 * @property-read Ticket $ticket
 */
class Attachment extends ActiveRecord
{
    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '{{%ticket_attachment}}';
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [['ticket_id', 'comment_id', 'size'], 'integer'],
            [['name', 'mime_type', 'unique_id'], 'string', 'max' => 255],
            ['path', 'string', 'max' => 512],
            ['unique_id', 'unique'],
            ['unique_id', 'default', 'value' => function ($model) {
                /* @var $model static */
                return sprintf(
                    '%s-%s',
                    $model->size,
                    preg_replace('/[^0-9a-zA-Z_-]/i', '', $model->name)
                );
            }],
            [
                'ticket_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => Ticket::class,
                'targetAttribute' => ['ticket_id' => 'id']
            ],
            [
                'comment_id',
                'exist',
                'skipOnError' => true,
                'targetClass' => Comment::class,
                'targetAttribute' => ['comment_id' => 'id']
            ],

            [['unique_id', 'name', 'path', 'mime_type', 'size'], 'required']
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_by', 'updated_by'],
                    self::EVENT_BEFORE_UPDATE => 'updated_by'
                ]
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    self::EVENT_BEFORE_UPDATE => 'updated_at'
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('simialbi/ticket/model/attachment', 'ID'),
            'ticket_id' => Yii::t('simialbi/ticket/model/attachment', 'Ticket ID'),
            'name' => Yii::t('simialbi/ticket/model/attachment', 'Name'),
            'path' => Yii::t('simialbi/ticket/model/attachment', 'Path'),
            'mime_type' => Yii::t('simialbi/ticket/model/attachment', 'Mime Type'),
            'size' => Yii::t('simialbi/ticket/model/attachment', 'Size'),
            'created_by' => Yii::t('simialbi/ticket/model/attachment', 'Created By'),
            'updated_by' => Yii::t('simialbi/ticket/model/attachment', 'Updated By'),
            'created_at' => Yii::t('simialbi/ticket/model/attachment', 'Created At'),
            'updated_at' => Yii::t('simialbi/ticket/model/attachment', 'Updated At'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function beforeDelete()
    {
        if (file_exists($this->localPath)) {
            FileHelper::unlink($this->localPath);
        }

        return parent::beforeDelete();
    }

    /**
     * Get attachment icon
     * @return string
     */
    public function getIcon()
    {
        switch ($this->mime_type) {
            case 'image/png':
            case 'image/jpeg':
            case 'image/gif':
            case 'image/wbmp':
            case 'image/bmp':
                return 'image';
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.template':
            case 'application/vnd.ms-word.document.macroEnabled.12':
            case 'application/vnd.ms-word.template.macroEnabled.12':
                return 'file-word';
            case 'application/msexcel':
            case 'application/vnd.ms-excel':
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
            case 'application/vnd.ms-excel.sheet.macroEnabled.12';
            case 'application/vnd.ms-excel.template.macroEnabled.12';
            case 'application/vnd.ms-excel.addin.macroEnabled.12';
            case 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
                return 'file-excel';
            case 'application/mspowerpoint':
            case 'application/vnd.ms-powerpoint':
            case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
            case 'application/vnd.openxmlformats-officedocument.presentationml.template':
            case 'application/vnd.openxmlformats-officedocument.presentationml.slideshow':
            case 'application/vnd.ms-powerpoint.addin.macroEnabled.12':
            case 'application/vnd.ms-powerpoint.presentation.macroEnabled.12':
            case 'application/vnd.ms-powerpoint.template.macroEnabled.12':
            case 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12':
                return 'file-powerpoint';
            case 'application/pdf':
                return 'file-pdf';
            case 'application/json':
            case 'application/javascript':
            case 'application/xhtml+xml':
            case 'application/xml':
            case 'application/x-httpd-php':
            case 'text/css':
            case 'text/html':
            case 'text/javascript':
            case 'text/xml':
                return 'file-code';
            case 'video/mpeg':
            case 'video/mp4':
            case 'video/ogg':
            case 'video/quicktime':
            case 'video/vnd.vivo':
            case 'video/webm':
            case 'video/x-msvideo':
            case 'video/x-sgi-movie':
                return 'video';
            default:
                return 'file';
        }
    }


    /**
     * Get local file path of file
     * @return string
     */
    public function getLocalPath()
    {
        $web = Yii::getAlias('@web');
        $webRoot = Yii::getAlias('@webroot');
        if (empty($web)) {
            return str_replace('/', DIRECTORY_SEPARATOR, $webRoot . $this->path);
        }

        return str_replace([$web, '/'], [$webRoot, DIRECTORY_SEPARATOR], $this->path);
    }

    /**
     * Get associated tickets
     * @return \yii\db\ActiveQuery
     */
    public function getTicket()
    {
        return $this->hasOne(Ticket::class, ['id' => 'ticket_id']);
    }
}
