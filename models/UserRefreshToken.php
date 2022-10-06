<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_refresh_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $refresh_token
 * @property string $user_ip
 * @property string $user_agent
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class UserRefreshToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_refresh_token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'refresh_token', 'user_ip', 'user_agent', 'created_at', 'updated_at'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['refresh_token'], 'string', 'max' => 40],
            [['user_ip'], 'string', 'max' => 50],
            [['user_agent'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'refresh_token' => 'Refresh Token',
            'user_ip' => 'User Ip',
            'user_agent' => 'User Agent',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}
