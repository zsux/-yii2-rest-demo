<?php

namespace app\components\base;

use yii\base\UserException;

/**
 * 服务异常类
 */
class ServiceException extends UserException
{
    /**
     * @inheritdoc
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        $code = (int)$code;

        if ($code < 1) {
            $code = 1;
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * 返回 response
     *
     * @return Response
     */
    public function response()
    {
        return new Response(['message' => $this->message, 'code' => $this->code]);
    }
}
