<?php

namespace app\models;

use app\components\JWT;
use Yii;
use yii\helpers\Json;

/**
* This is the model class for table "user".
*
* @property int $id
* @property string $username
* @property string $nickname
* @property string $password
* @property string $avatar
* @property string $created_at
* @property string|null $updated_at
*/
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public $authKey;

    public function rules()
    {
        return [
            [['username', 'nickname', 'password', 'created_at'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['username', 'nickname'], 'string', 'max' => 30],
            [['password', 'avatar'], 'string', 'max' => 255],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password']);
        return $fields;
    }

    public static function tableName()
    {
        return 'user';
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'nickname' => 'Nickname',
            'password' => 'Password',
            'avatar' => 'Avatar',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        try {
            $payload = JWT::decode($token, Yii::$app->params['jwt-key']);
            $user = Json::decode($payload['user']);
            return new static($user);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }
}
