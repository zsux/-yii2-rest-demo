<?php

namespace app\components\base;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord as BaseActiveRecord;
use yii\db\Exception;
use yii\base\InvalidParamException;

/**
 * ActiveRecord 基础类
 */
class ActiveRecord extends BaseActiveRecord
{
    /**
     * 自动更新 ctime、utime
     *
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = [];

        if ($this->hasAttribute('ctime') || $this->hasAttribute('utime')) {
            $attributes = [];

            if ($this->hasAttribute('ctime')) {
                $attributes[ActiveRecord::EVENT_BEFORE_INSERT][] = 'ctime';
            }

            if ($this->hasAttribute('utime')) {
                $attributes[ActiveRecord::EVENT_BEFORE_INSERT][] = 'utime';
                $attributes[ActiveRecord::EVENT_BEFORE_UPDATE][] = 'utime';
            }

            $behaviors[] = [
                'class' => TimestampBehavior::className(),
                'attributes' => $attributes,
                'value' => date('Y-m-d H:i:s'),
            ];
        }

        return $behaviors;
    }

    /**
     * insert, 若验证失败, 抛出异常, 直接交给控制器输出错误
     *
     * @param bool $runValidation
     * @param null $attributes
     * @return bool
     * @throws Exception
     * @throws ServiceException
     */
    public function insert($runValidation = true, $attributes = null)
    {
        $result = parent::insert($runValidation, $attributes);

        if ($result === false) {
            if ($this->hasErrors()) {
                $attribute = key($this->getErrors());
                throw new ServiceException(parent::getFirstError($attribute));
            }
        }

        return $result;
    }

    /**
     * update, 若验证失败, 抛出异常, 直接交给控制器输出错误
     *
     * @param bool $runValidation
     * @param null $attributeNames
     * @return bool|int
     * @throws Exception
     * @throws ServiceException
     */
    public function update($runValidation = true, $attributeNames = null)
    {
        $result = parent::update($runValidation, $attributeNames);

        if ($result === false) {
            if ($this->hasErrors()) {
                $attribute = key($this->getErrors());
                throw new ServiceException(parent::getFirstError($attribute));
            }
        }

        return $result;
    }

    /**
     * 去除字段左右的空格
     *
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        foreach ($this->getAttributes() as $name => $value) {
            if (null !== $value) {
                $this->setAttribute($name, trim($value));
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * 只要错误就结束, 避免浪费
     *
     * @inheritdoc
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }

        if (!$this->beforeValidate()) {
            return false;
        }

        $scenarios = $this->scenarios();
        $scenario = $this->getScenario();
        if (!isset($scenarios[$scenario])) {
            throw new InvalidParamException("Unknown scenario: $scenario");
        }

        if ($attributeNames === null) {
            $attributeNames = $this->activeAttributes();
        }

        foreach ($this->getActiveValidators() as $validator) {
            $validator->validateAttributes($this, $attributeNames);

            // 尽早结束验证, 只要有一处错误, 就停止验证
            if ($this->hasErrors()) {
                break;
            }
        }
        $this->afterValidate();

        return !$this->hasErrors();
    }

    /**
     * 返回表所有字段
     *
     * Source::getColumnNames('a', 'Source_')
     *
     * array(4) {
     *   [0]=>
     *   string(17) "a.id as Source_id"
     *   [1]=>
     *   string(21) "a.name as Source_name"
     *   [2]=>
     *   string(23) "a.ctime as Source_ctime"
     *   [3]=>
     *   string(23) "a.utime as Source_utime"
     * }
     *
     * @param null $alias
     * @param null $asPrefix
     * @return array
     */
    public static function getColumnNames($alias = null, $asPrefix = null)
    {
        return array_map(function ($name) use ($alias, $asPrefix) {
            return ($alias ? ($alias . '.' . $name) : $name) . ($asPrefix ? (' as ' . $asPrefix . $name) : '');
        }, static::getTableSchema()->getColumnNames());
    }
}
