<?php

namespace app\components\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

/**
 * Access Filter
 *
 * token: TOKEN
 */
class AccessFilter extends ActionFilter
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $headers = Yii::$app->request->headers;

        if (Yii::$app->params['token'] != $headers->get('token')) {
            throw new ForbiddenHttpException();
        }

        return parent::beforeAction($action);
    }
}
