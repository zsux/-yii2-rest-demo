<?php

namespace app\components\base;

/**
 * 调用接口方法不允许服务异常
 */
class MethodNotAllowedServiceException extends ServiceException
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct('Method Not Allowed', 405);
    }
}
