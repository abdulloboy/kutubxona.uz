<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "card".
 *
 * @property integer $id
 * @property integer $book_id
 * @property integer $user_id
 * @property integer $got_at
 * @property integer $returned_at
 *
 * @property User $user
 * @property Book $book
 */
class Card extends ActiveRecord
{

    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'card';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['book_id', 'user_id'], 'required'],
            [['book_id', 'user_id', 'got_at','returned_at'], 'integer'],
                
            [['user.name','book.name'],'safe'],
               
        ];
    }

    public function attributes()
    {
         return array_merge(parent::attributes(), ['user.name','book.name',]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'book_id' => Yii::t('app', 'Book ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'got_at' => Yii::t('app', 'Got At'),
            'returned_at' => Yii::t('app', 'Returned At'),


            'user.name' => Yii::t('app', 'User'),
            'book.name' => Yii::t('app', 'Book'),

        ];
    }
    
    public function behaviors()
    {
        return [
            'timestamp' => [
                 'class' => 'yii\behaviors\TimestampBehavior',
                 'attributes' => [
                     ActiveRecord::EVENT_BEFORE_INSERT => ['got_at'],
                     ActiveRecord::EVENT_BEFORE_UPDATE => [],
                ],
            ],
        ];
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
    public function getBook()
    {
        return $this->hasOne(Book::className(), ['id' => 'book_id']);
    }
}
