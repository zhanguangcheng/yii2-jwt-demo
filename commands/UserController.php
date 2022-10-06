<?php

namespace app\commands;

use app\models\User;
use yii\console\Controller;
use yii\console\ExitCode;

class UserController extends Controller
{
    public function actionIndex()
    {
        
    }

    public function actionCreateUser()
    {
        $model = new User([
            'username' => '12345',
            'nickname' => 'Grass',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $model->setPassword('123456');
        if (!$model->save()) {
            return $model->getErrorSummary(false);
        }
        return ExitCode::OK;
    }
}