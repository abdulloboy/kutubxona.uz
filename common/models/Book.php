<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "book".
 *
 * @property integer $id
 * @property integer $language_id
 * @property integer $topic_id
 * @property integer $user_id
 * @property string $name
 * @property string $author
 * @property string $imageFileName
 * @property string $url
 * @property string $description
 *
 * @property Topic $topic
 * @property Language $language
 * @property User $user
 * @property Card[] $cards
 */
class Book extends \yii\db\ActiveRecord
{

    public $imageFileName_Fl;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'book';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['language_id', 'topic_id', 'user_id', 'name', 'url', 'description'], 'required'],
            [['language_id', 'topic_id', 'user_id'], 'integer'],
            [['name', 'author', 'imageFileName', 'url', 'description'], 'string'],
                
            [['topic.name','language.name','user.name',],'safe'],
                  [['imageFileName_Fl'],'file'],
               
        ];
    }

    public function attributes()
    {
         return array_merge(parent::attributes(), ['topic.name','language.name','user.name',]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'language_id' => Yii::t('app', 'Language ID'),
            'topic_id' => Yii::t('app', 'Topic ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Name'),
            'author' => Yii::t('app', 'Author'),
            'imageFileName' => Yii::t('app', 'Image File Name'),
            'url' => Yii::t('app', 'Url'),
            'description' => Yii::t('app', 'Description'),

            'imageFileName_Fl' => Yii::t('app', 'Image'),

            'topic.name' => Yii::t('app', 'Topic'),
            'language.name' => Yii::t('app', 'Language'),
            'user.name' => Yii::t('app', 'User'),

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['id' => 'language_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['book_id' => 'id']);
    }
}
