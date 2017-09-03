<?php

namespace user\controllers;

use Yii;
use app\components\base\Response;
use user\services\user\UserService;
use user\models\form\user\UserForm;
use user\components\base\Controller;

/**
 * 用户
 */
class UserController extends Controller
{
    /**
     * @inheritdoc
     */
    public function verbs()
    {
        return [
            'get-user-info' => ['get'],
            'get-user-list' => ['get'],
        ];
    }

    /**
     * 获取用户信息
     *
     * @return Response
     */
    public function actionGetUserInfo()
    {
        $param = Yii::$app->request->get();

        $form = new UserForm();
        $form->validateScenario($param, 'getUserInfo');

        $result = UserService::getInstance()->getUserInfo($form);
        return new Response(['data' => $result]);
    }

    /**
     * 获取用户列表
     *
     * @return Response
     */
    public function actionGetUserList()
    {
        $param = Yii::$app->request->get();

        $form = new UserForm();
        $form->validateScenario($param, 'getUserList');

        $result = UserService::getInstance()->getUserList($form);
        return new Response(['data' => $result]);
    }
}
