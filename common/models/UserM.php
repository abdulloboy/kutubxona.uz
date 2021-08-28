<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $name
 * @property string $fullName
 * @property string $department
 * @property string $position
 * @property string $telephone
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Book[] $books
 * @property Card[] $cards
 * @property Log[] $logs
 */
class UserM extends \yii\db\ActiveRecord
{

    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'auth_key', 'password_hash', 'email', 'created_at', 'updated_at'], 'required'],
            [['name', 'fullName', 'department', 'position', 'telephone', 'auth_key', 'password_hash', 'password_reset_token', 'email'], 'string'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['email'], 'unique'],
            [['name'], 'unique'],
            [['password_reset_token'], 'unique'],
                
            [[],'safe'],
               
        ];
    }

    public function attributes()
    {
         return array_merge(parent::attributes(), []);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'fullName' => Yii::t('app', 'Full Name'),
            'department' => Yii::t('app', 'Department'),
            'position' => Yii::t('app', 'Position'),
            'telephone' => Yii::t('app', 'Telephone'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'email' => Yii::t('app', 'Email'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),



        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBooks()
    {
        return $this->hasMany(Book::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogs()
    {
        return $this->hasMany(Log::className(), ['user_id' => 'id']);
    }
}
