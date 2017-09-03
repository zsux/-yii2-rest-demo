<?php

namespace app\components\base;

use Yii;

/**
 * Replace default prefix, record trace_id.
 */
class FileTarget extends \yii\log\FileTarget
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->prefix = function () {
            $traceId = '-';

            /* @var \mike\zipkin\Tracer $tracer */
            if ($tracer = Yii::$app->get('__tracer__', false)) {
                $traceId = $tracer->getTraceId();
            }

            return "[$traceId]";
        };
    }
}
