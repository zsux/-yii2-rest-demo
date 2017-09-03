<?php

namespace user\components\base;

use Yii;

/**
 * ActiveRecord for voucher module
 */
class ActiveRecord extends \app\components\base\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->get('db_jsqb');
    }
}
