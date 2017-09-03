<?php

namespace app\components\base;

use Yii;
use yii\filters\VerbFilter;
use yii\base\ErrorException;
use yii\web\Response as WebResponse;
use app\components\filters\AccessFilter;

/**
 * 接口控制器基类
 */
class Controller extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessFilter::className(),
                'except' => ['error'],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ],
        ];
    }

    /**
     * HTTP verbs
     *
     * @return array
     */
    public function verbs()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function runAction($id, $params = [])
    {
        try {
            $result = parent::runAction($id, $params);
        } catch (ServiceException $e) {
            $result = $e->response();
        }

        if (!$result instanceof Response) {
            throw new ErrorException('Response must be \app\components\base\Response instance.');
        }

        Yii::$app->response->format = WebResponse::FORMAT_JSON;

        return $result->toArray();
    }
}
