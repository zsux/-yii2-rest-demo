<?php

namespace app\components\base;

use Yii;
use yii\base\InvalidRouteException;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Error Handler
 *
 * 处理输出内容
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function renderException($exception)
    {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
            // reset parameters of response to avoid interference with partially created response data
            // in case the error occurred while sending the response.
            $response->isSent = false;
            $response->stream = null;
            $response->data = null;
            $response->content = null;
        } else {
            $response = new Response();
        }

        $response->format = Response::FORMAT_JSON;

        if ($exception instanceof InvalidRouteException || $exception instanceof NotFoundHttpException) {
            $response->data = (new NotFoundServiceException())->response()->toArray();
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $response->data = (new MethodNotAllowedServiceException())->response()->toArray();
        } elseif ($exception instanceof ForbiddenHttpException) {
            $response->data = (new ForbiddenServiceException())->response()->toArray();
        } else {
            if (YII_ENV_PROD) {
                $response->data = (new SystemErrorServiceException())->response()->toArray();
            } else {
                $response->format = Response::FORMAT_HTML;
                throw $exception;
            }
        }

        $response->send();
    }
}
