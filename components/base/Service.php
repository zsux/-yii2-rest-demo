<?php

namespace app\components\base;

use yii\base\Component;
use yii\db\Query;
use yii\db\Connection;
use yii\helpers\StringHelper;

/**
 * Service
 */
class Service extends Component
{
    /**
     * 返回 service 单例
     *
     * @return static
     */
    public static function getInstance()
    {
        static $instance;

        if ($instance === null) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * 返回分页结果
     *
     * @param Query $query
     * @param Connection|null $db
     * @param int $pageNo
     * @param int $pageSize
     * @param callable|null $handle
     * @return array
     */
    protected function pageQuery(
        Query $query,
        Connection $db = null,
        $pageNo = 1,
        $pageSize = 100,
        callable $handle = null
    ) {
        $pageNo = intval($pageNo);
        $pageNo = $pageNo > 0 ? $pageNo : 1;
        $pageSize = intval($pageSize);
        $pageSize = $pageSize > 0 ? $pageSize : 10;

        $countQuery = clone $query;
        $totalResults = intval($countQuery->count('*', $db));

        $query->offset(($pageNo - 1) * $pageSize);
        $query->limit($pageSize);
        $list = $query->all($db);

        if ($list && $handle) {
            if (($result = call_user_func_array($handle, [&$list])) !== null) {
                $list = $result;
            }
        }

        return [
            'total_results' => $totalResults,
            'has_next' => $totalResults > ($pageNo * $pageSize),
            'page_no' => $pageNo,
            'page_size' => $pageSize,
            'list' => $list,
        ];
    }

    /**
     * 返回分页结果
     *
     * @param array $list
     * @param $totalResults
     * @param int $pageNo
     * @param int $pageSize
     * @return array
     */
    protected function pageResult(array $list = [], $totalResults = 0, $pageNo = 1, $pageSize = 100)
    {
        return [
            'total_results' => $totalResults,
            'has_next' => $totalResults > ($pageNo * $pageSize),
            'page_no' => $pageNo,
            'page_size' => $pageSize,
            'list' => $list
        ];
    }

    /**
     * 字段重整，用于对 join 产生的数据重整为对象格式，map 的映射一定要明显
     *
     * [['name' => 'xx', 'xxx_name' => 'yyy']];
     *
     * fieldReorganize($a, ['xxx_' => 'xxx'])
     *
     * [['name' => 'xx', 'xxx' => ['name' => 'yyy']]]
     *
     * @param array $data
     * @param array $map
     */
    public function fieldReorganize(array &$data, array $map)
    {
        if (!$data || !$map) {
            return;
        }

        foreach ($data as $key => &$value) {
            if (is_integer($key)) {
                $this->fieldReorganize($value, $map);
                continue;
            }

            foreach ($map as $k => $v) {
                if (StringHelper::startsWith($key, $k)) {
                    if (!isset($data[$v])) {
                        $data[$v] = [];
                    }
                    $data[$v][str_replace($k, '', $key)] = $value;
                    unset($data[$key]);
                }
            }
        }
    }
}
