<?php

namespace user\models\db;

use user\components\base\ActiveRecord;

/**
 * This is the model class for table "{{%admin_user}}".
 *
 * @property integer $id
 * @property string $username
 * @property string $phone
 * @property string $password
 * @property string $role
 * @property string $created_user
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $mark
 * @property integer $callcenter
 */
class AdminUser extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'phone', 'password', 'role', 'created_user', 'mark'], 'required'],
            [['role'], 'string'],
            [['created_at', 'updated_at', 'callcenter'], 'integer'],
            [['username', 'created_user'], 'string', 'max' => 30],
            [['phone'], 'string', 'max' => 13],
            [['password'], 'string', 'max' => 100],
            [['mark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '用户名',
            'phone' => '手机号',
            'password' => '登录密码',
            'role' => '角色',
            'created_user' => '创建人',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'mark' => '标记',
            'callcenter' => '是否是催收人员，1是，0不是',
        ];
    }
}
