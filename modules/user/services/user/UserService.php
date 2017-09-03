<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 01/09/2017
 * Time: 11:32
 */

namespace user\services\user;

use user\models\db\AdminUser;
use user\components\base\Service;
use user\models\form\user\UserForm;
use app\components\helpers\ArrayHelper;

class UserService extends Service
{
    const NOCALLCENTERNAME = '不是催收员';

    const YESCALLCENTERNAME = '催收员';

    /**
     * 用户列表
     *
     * @param UserForm $form
     * @return array
     */
    public function getUserList(UserForm $form)
    {
        $query = AdminUser::find();
        $query->filterWhere(['username' => $form->username])->orderBy(['id' => SORT_DESC]);

        return $this->pageQuery(
            $query,
            AdminUser::getDb(),
            $form->page,
            $form->size,
            [$this, 'userListHandle']
        );
    }

    /**
     * 用户数据处理
     *
     * @param $list
     * @return array
     */
    protected function userListHandle($list): array
    {
        $data = ArrayHelper::toArray($list);
        foreach ($data as &$item) {
            $item['callcenterName'] = self::NOCALLCENTERNAME;
            if ($item['callcenter'] === 1) {
                $item['callcenterName'] = self::YESCALLCENTERNAME;
            }
            unset($item['password']);
        }

        return $data;
    }

    /**
     * 用户基本信息
     *
     * @param UserForm $form
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getUserInfo(UserForm $form)
    {
        $query = AdminUser::find();
        $query->filterWhere(['id' => $form->id]);
        $query->andFilterWhere(['username' => $form->username]);
        $query->andFilterWhere(['phone' => $form->phone]);

        return $query->one();
    }
}
