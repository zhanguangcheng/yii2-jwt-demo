<?php

namespace app\controllers;

use app\components\JWT;
use app\models\LoginForm;
use app\models\User;
use app\models\UserRefreshToken;
use UnexpectedValueException;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class UserController extends BaseController
{
    protected function verbs()
    {
        return [
            'login' => ['POST'],
            'logout' => ['POST'],
            'refresh-token' => ['POST'],
            '*' => ['GET'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function optional()
    {
        return ['login', 'refresh-token'];
    }

    /**
     * 登录
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return [
                'code' => 400,
                'message' => '已经登录过了',
            ];
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(), '') && $model->login()) {
            $user = $model->user;
            return [
                'code' => 200,
                'result' => [
                    'user' => $user->getAttributes(null, ['password']),
                    'access_tokekn' => $this->generateAccessToken($user),
                    'refresh_token' => $this->generateRefreshToken($user),
                ]
            ];
        }
        return [
            'code' => 400,
            'message' => $model->hasErrors() ? implode('、', $model->getErrorSummary(false)) : '登录失败'
        ];
    }

    /**
     * 退出
     */
    public function actionLogout()
    {
        $condition = ['user_id' => Yii::$app->user->id];
        $refreshToken = Yii::$app->request->post('refresh-token');
        if ($refreshToken) {
            $condition['refresh_token'] = $refreshToken;
        }
        UserRefreshToken::deleteAll($condition);
        Yii::$app->user->logout();
        return [
            'code' => 200,
            'message' => '退出成功',
        ];
    }

    /**
     * 刷新access-token
     */
    public function actionRefreshToken()
    {
        $refreshToken = Yii::$app->request->post('refresh-token');
        try {
            $refreshJwt = JWT::decode($refreshToken, Yii::$app->params['jwt-key']);
        } catch(UnexpectedValueException $e) {
            switch ($e->getCode()) {
                case 3:
                    throw new ForbiddenHttpException('签发时间错误');
                    break;
                case 4:
                    throw new ForbiddenHttpException('refreshToken已过期');
                    break;
                default:
                    throw $e;
                    break;
            }
        }
        $refresh = UserRefreshToken::findOne(['user_id' => $refreshJwt['uid'], 'refresh_token' => $refreshJwt['token']]);
        if (empty($refresh)) {
            return [
                'code' => 400,
                'message' => 'refreshToken已失效',
            ];
        }
        $user = User::findIdentity($refresh->user_id);
        if (empty($user)) {
            return [
                'code' => 400,
                'message' => '用户已经不存在',
            ];
        }
        $accessToken = $this->generateAccessToken($user);
        return [
            'code' => 200,
            'result' => [
                'access_tokekn' => $accessToken,
            ]
        ];
    }

    /**
     * 获取个人信息
     */
    public function actionInfo()
    {
        return [
            'code' => 200,
            'result' => Yii::$app->user->getIdentity(),
        ];
    }

    /**
     * 获取所有会话
     */
    public function actionSessions()
    {
        $query = UserRefreshToken::find()->select(['id', 'user_ip', 'user_agent', 'created_at', 'updated_at']);
        $models = $query->where(['user_id' => Yii::$app->user->id])->orderBy('id DESC')->asArray()->all();
        return [
            'code' => 200,
            'result' => $models,
        ];
    }

    /**
     * 生成accessToken
     * @param User $user
     * @return string
     */
    private function generateAccessToken(User $user)
    {
        $data = $user->getAttributes(['id', 'username', 'nickname']);
        $time = time();
        $accessTokenJwt = [
            'user' => Json::encode($data),
            'iat' => $time,
            'exp' => $time + 180,
        ];
        return JWT::encode($accessTokenJwt, Yii::$app->params['jwt-key']);
    }

    /**
     * 生成refreshToken
     * @param User $user
     * @return string
     */
    private function generateRefreshToken(User $user)
    {
        $condition = [
            'user_id' => $user->id,
            'user_agent' => Yii::$app->request->getUserAgent(),
        ];
        $model = UserRefreshToken::findOne($condition);
        if (!$model) {
            $model = new UserRefreshToken();
            $model->setAttributes($condition);
            $model->user_ip = Yii::$app->request->getUserIP();
            $model->created_at = date('Y-m-d H:i:s');
        }
        $model->refresh_token = Yii::$app->security->generateRandomString(40);
        $model->updated_at = date('Y-m-d H:i:s');
        if (!$model->save()) {
            throw new ServerErrorHttpException($model->getErrorSummary(false));
        }

        $time = time();
        $refreshTokenJwt = [
            'token' => $model->refresh_token,
            'uid' => $user->id,
            'iat' => $time,
            'exp' => $time + 300,
        ];
        return JWT::encode($refreshTokenJwt, Yii::$app->params['jwt-key']);
    }
}
