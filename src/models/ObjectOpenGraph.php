<?php

namespace DotPlant\OpenGraph\models;

use Yii;

/**
 * This is the model class for table "{{%object_open_graph}}".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property integer $active
 * @property string $title
 * @property string $description
 * @property string $image
 */
class ObjectOpenGraph extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%object_open_graph}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'object_model_id', 'title', 'description'], 'required'],
            [['object_id', 'object_model_id', 'active'], 'integer'],
            [['description'], 'string'],
            [['title', 'image'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'active' => Yii::t('app', 'Active'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'image' => Yii::t('app', 'Image'),
        ];
    }
}
