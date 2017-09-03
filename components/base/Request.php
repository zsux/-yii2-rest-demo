<?php

namespace app\components\base;

/**
 * Web Request
 */
class Request extends \yii\web\Request
{
    /**
     * @var string Form 表单提交的 raw 字段名
     */
    public $rawName = 'raw';

    /**
     * 使用 x-www-form-urlencoded 格式的 raw 参数 来模拟 raw 提交
     *
     * @inheritdoc
     */
    public function getRawBody()
    {
        $rawBody = $this->post($this->rawName, '');

        if ($rawBody) {
            $this->setRawBody($rawBody);
        }

        return parent::getRawBody();
    }

    /**
     * @inheritdoc
     */
    public function getUserIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return current(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        }

        return parent::getUserIP();
    }
}
