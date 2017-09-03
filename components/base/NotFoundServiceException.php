<?php

namespace app\components\base;

/**
 * 接口不存在服务异常
 */
class NotFoundServiceException extends ServiceException
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct('Not Found', 404);
    }
}
