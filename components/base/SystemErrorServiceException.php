<?php

namespace app\components\base;

/**
 * 系统错误服务异常
 */
class SystemErrorServiceException extends ServiceException
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct('Internal Server Error', '500');
    }
}
