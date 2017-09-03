<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 11/24/16
 * Time: 09:53
 */

namespace app\components\helpers;

use yii\helpers\BaseArrayHelper;

class ArrayHelper extends BaseArrayHelper
{
    /**
     * 获取数组中值为空的字段
     *
     * @param array $input
     * @return array
     */
    public static function arrayFilter(array $input)
    {
        return array_filter($input, function ($value) {
            return $value !== '' && $value !== null && $value !== 0;
        });
    }
}
