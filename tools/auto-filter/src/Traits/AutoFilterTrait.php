<?php

namespace Feiyun\Tools\AutoFilter\Traits;

use Feiyun\Tools\AutoFilter\Contracts\AutoFilterInterface;
use Feiyun\Tools\AutoFilter\Support\FieldTypeDetector;
use Feiyun\Tools\AutoFilter\Support\QueryBuilder;
use Hyperf\Database\Model\Relations\MorphTo;
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

            $forceExact = static::isExactMatchField($key);

            // 解析字段别名：将 _as_ / _only_ 开头的字段转换为实际字段名
            $actualKey = static::parseFieldAlias($key);

            if (!QueryBuilder::isFieldAllowed($actualKey, $whitelist, $blacklist)) {
                continue;
            }

            if (strpos($actualKey, '.') === false) {
                // 当前表字段
                if (array_key_exists($actualKey, $columnsTypeMap)) {
                    QueryBuilder::buildWhere($query, $actualKey, $value, $columnsTypeMap[$actualKey], $forceExact);
                }
            } else {
                // 关联表字段
                $this->buildRelationWhere($query, $actualKey, $value, $whitelist, $blacklist, $forceExact);
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
     * @param bool $forceExact
     * @return void
     */
    protected function buildRelationWhere($query, string $key, $value, array $whitelist, array $blacklist, bool $forceExact = false): void
    {
        $relations = explode('.', $key);
        $field = array_pop($relations);
        $relationPath = implode('.', $relations);

        if (empty($relationPath) || !$this->hasValidRelationPath($query, $relationPath)) {
            return;
        }

        $model = $query->getModel();
        $rootRelation = $relations[0];

        try {
            $rootRelationInstance = $model->{$rootRelation}();
        } catch (\Throwable $e) {
            return;
        }

        // 检查根关系是否为 MorphTo，若是则在 MorphTo 回调中处理后续关系
        if ($rootRelationInstance instanceof MorphTo) {
            $nestedPath = implode('.', array_slice($relations, 1));

            $query->whereHasMorph($rootRelation, '*', function ($q) use ($nestedPath, $field, $value, $key, $whitelist, $blacklist, $forceExact) {
                if (!QueryBuilder::isFieldAllowed($key, $whitelist, $blacklist)) {
                    return;
                }

                if ($nestedPath === '') {
                    if (!$this->applyFieldWhere($q, $field, $value, $forceExact)) {
                        $q->whereRaw('1 = 0');
                    }
                    return;
                }

                $nestedRootRelation = explode('.', $nestedPath)[0];
                if (!method_exists($q->getModel(), $nestedRootRelation)) {
                    $q->whereRaw('1 = 0');
                    return;
                }

                $q->whereHas($nestedPath, function ($nestedQ) use ($field, $value, $forceExact) {
                    if (!$this->applyFieldWhere($nestedQ, $field, $value, $forceExact)) {
                        $nestedQ->whereRaw('1 = 0');
                    }
                });
            });

            return;
        }

        // 普通关联（包含多级关系）直接使用 whereHas
        $query->whereHas($relationPath, function ($q) use ($field, $value, $key, $whitelist, $blacklist, $forceExact) {
            if (!QueryBuilder::isFieldAllowed($key, $whitelist, $blacklist)) {
                return;
            }

            if (!$this->applyFieldWhere($q, $field, $value, $forceExact)) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    /**
     * 在当前查询模型上按字段类型追加 where 条件
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param bool $forceExact
     * @return bool
     */
    protected function applyFieldWhere($query, string $field, $value, bool $forceExact = false): bool
    {
        $relatedModel = $query->getModel();
        $table = $relatedModel->getTable();
        $connection = $relatedModel->getConnectionName();

        $columnsTypeMap = FieldTypeDetector::getTableColumnsType($table, $connection);

        if (array_key_exists($field, $columnsTypeMap)) {
            QueryBuilder::buildWhere($query, $field, $value, $columnsTypeMap[$field], $forceExact);
            return true;
        }

        return false;
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

            // MorphTo 在查询阶段通常无法提前确定真实 related 模型，无法继续可靠验证后续路径
            if ($relationInstance instanceof MorphTo) {
                return true;
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
     * 但保留 _as_ / _only_ 开头的筛选字段
     * 
     * @param array $params
     * @return array
     */
    protected static function excludeSystemParams(array $params): array
    {
        return array_filter($params, function ($key) {
            // 如果以 _as_ / _only_ 开头，保留（这是筛选字段前缀）
            if (strpos($key, '_as_') === 0 || strpos($key, '_only_') === 0) {
                return true;
            }
            
            // 如果包含点号，检查最后一部分是否以 _as_ / _only_ 开头（关联表筛选字段）
            if (strpos($key, '.') !== false) {
                $parts = explode('.', $key);
                $lastPart = end($parts);
                if (strpos($lastPart, '_as_') === 0 || strpos($lastPart, '_only_') === 0) {
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
     * 将 _as_ / _only_ 开头的字段转换为实际字段名
     * 例如: taskResult._as_submit_staff_id -> taskResult.submit_staff_id
     * 例如: _only_goods_code -> goods_code
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

            $field = static::stripFieldPrefix($field);

            return $relationPath . '.' . $field;
        }

        return static::stripFieldPrefix($fieldName);
    }

    /**
     * 是否为精确匹配字段（_only_ 前缀）
     */
    protected static function isExactMatchField(string $fieldName): bool
    {
        $field = $fieldName;
        if (strpos($fieldName, '.') !== false) {
            $parts = explode('.', $fieldName);
            $field = array_pop($parts);
        }

        while ($field !== '') {
            if (strpos($field, '_only_') === 0) {
                return true;
            }

            if (strpos($field, '_as_') === 0) {
                $field = substr($field, 4);
                continue;
            }

            break;
        }

        return false;
    }

    /**
     * 去除字段名前缀（_as_ / _only_）
     */
    protected static function stripFieldPrefix(string $fieldName): string
    {
        $field = $fieldName;

        while ($field !== '') {
            if (strpos($field, '_as_') === 0) {
                $field = substr($field, 4);
                continue;
            }

            if (strpos($field, '_only_') === 0) {
                $field = substr($field, 6);
                continue;
            }

            break;
        }

        return $field;
    }
}
