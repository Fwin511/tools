<?php

namespace Feiyun\Tools\AutoFilter\Traits;

use Feiyun\Tools\AutoFilter\Contracts\AutoFilterInterface;
use Feiyun\Tools\AutoFilter\Support\FieldTypeDetector;
use Feiyun\Tools\AutoFilter\Support\QueryBuilder;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * 自动筛选 Trait
 *
 * @method  \Hyperf\Database\Model\Builder  autoFilter(array $blacklist = [], array $whitelist = [], array $asParam = [])
 */
trait AutoFilterTrait
{
    /**
     * 自动筛选
     * 用法: Model::query()->autoFilter($blacklist, $whitelist, ['field'=>'value'])->get();
     *
     * @param \Hyperf\Database\Model\Builder $query
     * @param array $blacklist 禁止字段
     * @param array $whitelist 允许字段
     * @param array $asParams 外部传递的搜索参数
     * @return  \Hyperf\Database\Model\Builder
     */
    public function scopeAutoFilter($query, array $blacklist = [], array $whitelist = [], array $asParams = [])
    {
        $params = static::getRequestParams();

        // 始终排除分页参数和系统参数
        $params = static::excludeParams($params, ['page', 'page_size', 'per_page']);
        
        // 排除以单个下划线开头的系统参数（但保留 _as_ 别名字段）
        $params = static::excludeSystemParams($params);

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
            // 跳过空值和空数组
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                continue;
            }

            // 如果是数组，过滤掉空元素
            if (is_array($value)) {
                $value = array_filter($value, function ($v) {
                    return $v !== null && $v !== '';
                });
                // 如果过滤后数组为空，跳过
                if (empty($value)) {
                    continue;
                }
            }

            // 解析字段别名：将 _as_ 开头的字段转换为实际字段名
            $actualKey = static::parseFieldAlias($key);

            if (!QueryBuilder::isFieldAllowed($actualKey, $whitelist, $blacklist)) {
                continue;
            }

            if (strpos($actualKey, '.') === false) {
                // 当前表字段
                if (array_key_exists($actualKey, $columnsTypeMap)) {
                    QueryBuilder::buildWhere($query, $actualKey, $value, $columnsTypeMap[$actualKey]);
                }
            } else {
                // 关联表字段
                $this->buildRelationWhere($query, $actualKey, $value, $whitelist, $blacklist);
            }
        }

        return $query;
    }

    /**
     * 调试自动筛选 - 输出生成的 SQL 和参数
     * 用法: Model::query()->autoFilter()->debugAutoFilter();
     *
     * @param \Hyperf\Database\Model\Builder $query
     * @return array
     */
    public function scopeDebugAutoFilter($query): array
    {
        return [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'raw_sql' => $this->getRawSql($query)
        ];
    }

    /**
     * 获取完整的 SQL 语句（包含参数值）
     */
    protected function getRawSql($query): string
    {
        $sql = $query->toSql();
        $bindings = $query->getBindings();

        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'{$binding}'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
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

        if (empty($relationPath) || !$this->hasValidRelationPath($query, $relationPath)) {
            return;
        }

        $model = $query->getModel();
        $relationInstance = $model->{$relationPath}();

        // 检查是否为 MorphTo 关联
        if ($relationInstance instanceof \Hyperf\Database\Model\Relations\MorphTo) {
            // 对于 MorphTo 关联，使用 whereHasMorph
            $query->whereHasMorph($relationPath, '*', function ($q) use ($field, $value, $key, $whitelist, $blacklist) {
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
        } else {
            // 普通关联使用 whereHas
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

    /**
     * 检查关联路径是否有效
     * 例如：taskResult.user.name 会依次验证 taskResult 和 user 关系是否存在
     *
     * @param mixed $query
     * @param string $relationPath
     * @return bool
     */
    protected function hasValidRelationPath($query, string $relationPath): bool
    {
        $relations = explode('.', $relationPath);
        $model = $query->getModel();

        foreach ($relations as $relation) {
            if ($relation === '' || !method_exists($model, $relation)) {
                return false;
            }

            try {
                $relationInstance = $model->{$relation}();
            } catch (\Throwable $e) {
                return false;
            }

            if (!is_object($relationInstance) || !method_exists($relationInstance, 'getRelated')) {
                return false;
            }

            $relatedModel = $relationInstance->getRelated();
            if (!is_object($relatedModel)) {
                return false;
            }

            $model = $relatedModel;
        }

        return true;
    }

    /**
     * 获取请求参数
     */
    protected static function getRequestParams(): array
    {
        try {
            // 兼容 Hyperf 2.x (Hyperf\Utils\ApplicationContext) 和 3.x (Hyperf\Context\ApplicationContext)
            if (class_exists(\Hyperf\Context\ApplicationContext::class)) {
                $container = \Hyperf\Context\ApplicationContext::getContainer();
            } else {
                $container = \Hyperf\Utils\ApplicationContext::getContainer();
            }
            $request = $container->get(RequestInterface::class);
            return $request->all();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 排除指定参数
     */
    protected static function excludeParams(array $params, array $excludeKeys): array
    {
        return array_diff_key($params, array_flip($excludeKeys));
    }

    /**
     * 排除以单个下划线开头的系统参数
     * 但保留 _as_ 开头的别名字段
     * 
     * @param array $params
     * @return array
     */
    protected static function excludeSystemParams(array $params): array
    {
        return array_filter($params, function ($key) {
            // 如果以 _as_ 开头，保留（这是别名字段）
            if (strpos($key, '_as_') === 0) {
                return true;
            }
            
            // 如果包含点号，检查最后一部分是否以 _as_ 开头（关联表别名）
            if (strpos($key, '.') !== false) {
                $parts = explode('.', $key);
                $lastPart = end($parts);
                if (strpos($lastPart, '_as_') === 0) {
                    return true;
                }
            }
            
            // 排除其他以单个下划线开头的系统参数（如 _sort, _filter 等）
            if (strpos($key, '_') === 0) {
                return false;
            }
            
            return true;
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * 解析字段别名
     * 将 _as_ 开头的字段转换为实际字段名
     * 例如: taskResult._as_submit_staff_id -> taskResult.submit_staff_id
     *
     * @param string $fieldName
     * @return string
     */
    protected static function parseFieldAlias(string $fieldName): string
    {
        // 检查是否包含点号（关联表字段）
        if (strpos($fieldName, '.') !== false) {
            // 分离关联路径和字段名
            $parts = explode('.', $fieldName);
            $field = array_pop($parts);
            $relationPath = implode('.', $parts);

            // 处理字段名的别名
            if (strpos($field, '_as_') === 0) {
                $field = substr($field, 4); // 移除 _as_ 前缀
            }

            return $relationPath . '.' . $field;
        }

        // 处理普通字段的别名
        if (strpos($fieldName, '_as_') === 0) {
            return substr($fieldName, 4); // 移除 _as_ 前缀
        }

        return $fieldName;
    }
}
