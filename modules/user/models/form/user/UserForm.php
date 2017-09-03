<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 01/09/2017
 * Time: 13:41
 */

namespace user\models\form\user;

use app\components\base\Form;

class UserForm extends Form
{
    /**
     * @var integer 用户id
     */
    public $id;

    /**
     * @var integer 当前页码
     */
    public $page;

    /**
     * @var integer 每页返回记录数
     */
    public $size;

    /**
     * @var string 用户手机号
     */
    public $phone;

    /**
     * @var string 用户名称
     */
    public $username;

    /**
     * @var string 登陆密码
     */
    public $password;

    /**
     *  获取用户列表
     */
    const GET_USER_LIST = 'getUserList';

    /**
     *  获取用户信息
     */
    const GET_USER_INFO = 'getUserInfo';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'integer', 'min' => 1, 'max' => 11, 'on' => [
                self::GET_USER_LIST,
                self::GET_USER_INFO
            ]],

            ['phone', 'mobile', 'on' => [self::GET_USER_LIST]],

            ['username', 'required', 'on' => [self::GET_USER_INFO]],
            ['username', 'string', 'on' => [
                self::GET_USER_LIST,
                self::GET_USER_INFO
            ]],

            ['password', 'required', 'on' => [self::GET_USER_INFO]],
            ['password', 'password', 'on' => [self::GET_USER_INFO]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::GET_USER_INFO => ['id', 'username', 'password'],
            self::GET_USER_LIST => ['id', 'page', 'size', 'phone', 'username'],
        ];
    }
}
