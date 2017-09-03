<?php

namespace app\components\base;

use yii\base\Component;
use yii\helpers\Json;

/**
 * 接口响应类
 *
 * return Response(['code' => 100, 'message' => 'xxx', 'data' => null]);
 *
 */
class Response extends Component
{
    /**
     * @var int Api Code
     */
    private $code = 0;

    /**
     * @var string Api Message
     */
    private $message = '请求成功';

    /**
     * @var mixed Api Data
     */
    private $data = null;

    /**
     * Get code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get Data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set Code
     *
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = (int)$code;
    }

    /**
     * Set Message
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = (string)$message;
    }

    /**
     * Set Data
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'data' => $this->getData(),
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return Json::encode($this->toArray());
    }
}
