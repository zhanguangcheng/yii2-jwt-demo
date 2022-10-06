<?php

namespace app\controllers;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\Response;

class BaseController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::class,
                'actions' => $this->verbs(),
            ],
            'authenticator' => [
                'class' => CompositeAuth::class,
                'optional' => $this->optional(),
                'authMethods' => [
                    ['class' => HttpBearerAuth::class],
                    ['class' => QueryParamAuth::class],
                ],
            ],
        ];
    }

    /**
     * 无需登录验证的action
     */
    protected function optional()
    {
        return [];
    }

}