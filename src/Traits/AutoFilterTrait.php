<?php

namespace Feiyun\AutoFilter\Traits;

use Feiyun\AutoFilter\Contracts\AutoFilterInterface;
use Feiyun\AutoFilter\Support\FieldTypeDetector;
use Feiyun\AutoFilter\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * 自动筛选 Trait
 * 
 * @method mixed autoFilter(array $blacklist = [], array $whitelist = [], array $asParam = [])
 */
trait AutoFilterTrait
{
    /**
     * 自动筛选
     * 用法: Model::query()->autoFilter($blacklist, $whitelist, ['field'=>'value'])->get();
     *
     * @param mixed $query
     * @param array $blacklist 禁止字段
     * @param array $whitelist 允许字段
     * @param array $asParams 外部传递的搜索参数
     * @return mixed
     */
    public function scopeAutoFilter($query, array $blacklist = [], array $whitelist = [], array $asParams = [])
    {
        $request = app(Request::class);
        $params = $request->all();

        // 始终排除分页参数
        $params = Arr::except($params, ['page', 'page_size', 'per_page']);

        if (empty($params) && empty($asParams)) {
            return $query;
        }

        // 合并外部参数，外部参数优先级更高
        if (!empty($asParams)) {
            $params = array_merge($params, $asParams);
        }

        $model = $query->getModel();
        $table = $model->getTable();
        $connection = $model->getConnectionName();

        // 获取表字段类型映射
        $columnsTypeMap = FieldTypeDetector::getTableColumnsType($table, $connection);

        foreach ($params as $key => $value) {
            // 跳过空值
            if ($value === null || $value === '') {
                continue;
            }

            if (!QueryBuilder::isFieldAllowed($key, $whitelist, $blacklist)) {
                continue;
            }

            if (strpos($key, '.') === false) {
                // 当前表字段
                if (array_key_exists($key, $columnsTypeMap)) {
                    QueryBuilder::buildWhere($query, $key, $value, $columnsTypeMap[$key]);
                }
            } else {
                // 关联表字段
                $this->buildRelationWhere($query, $key, $value, $whitelist, $blacklist);
            }
        }

        return $query;
    }

    /**
     * 构建关联表查询条件
     *
     * @param mixed $query
     * @param string $key
     * @param mixed $value
     * @param array $whitelist
     * @param array $blacklist
     * @return void
     */
    protected function buildRelationWhere($query, string $key, $value, array $whitelist, array $blacklist): void
    {
        $relations = explode('.', $key);
        $field = array_pop($relations);
        $relationPath = implode('.', $relations);

        $query->whereHas($relationPath, function ($q) use ($field, $value, $key, $whitelist, $blacklist) {
            if (!QueryBuilder::isFieldAllowed($key, $whitelist, $blacklist)) {
                return;
            }

            $relatedModel = $q->getModel();
            $table = $relatedModel->getTable();
            $connection = $relatedModel->getConnectionName();
            
            $columnsTypeMap = FieldTypeDetector::getTableColumnsType($table, $connection);

            if (array_key_exists($field, $columnsTypeMap)) {
                QueryBuilder::buildWhere($q, $field, $value, $columnsTypeMap[$field]);
            }
        });
    }
}
