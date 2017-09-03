<?php

namespace app\components\base;

/**
 * 无权限访问服务异常
 */
class ForbiddenServiceException extends ServiceException
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct('Forbidden', 403);
    }
}
