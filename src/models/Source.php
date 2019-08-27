<?php

namespace simialbi\yii2\ticket\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%ticket_source}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $created_by
 * @property string $updated_by
 * @property integer|string $created_at
 * @property integer|string $updated_at
 *
 * @property-read Ticket[] $tickets
 */
class Source extends \yii\db\ActiveRecord
{
    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '{{%ticket_source}}';
    }

    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => 255],

            ['name', 'required']
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
            'id' => Yii::t('simialbi/ticket/model/source', 'ID'),
            'name' => Yii::t('simialbi/ticket/model/source', 'Name'),
            'created_by' => Yii::t('simialbi/ticket/model/source', 'Created By'),
            'updated_by' => Yii::t('simialbi/ticket/model/source', 'Updated By'),
            'created_at' => Yii::t('simialbi/ticket/model/source', 'Created At'),
            'updated_at' => Yii::t('simialbi/ticket/model/source', 'Updated At'),
        ];
    }

    /**
     * Get associated tickets
     * @return \yii\db\ActiveQuery
     */
    public function getTickets()
    {
        return $this->hasMany(Ticket::class, ['source_id' => 'id']);
    }
}
