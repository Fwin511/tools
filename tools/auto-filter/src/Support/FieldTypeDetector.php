<?php

namespace Feiyun\Tools\AutoFilter\Support;

use Hyperf\DbConnection\Db;
use Hyperf\Context\ApplicationContext;
use Psr\SimpleCache\CacheInterface;

class FieldTypeDetector
{
    /**
     * 获取表字段类型映射（带缓存）
     *
     * @param string $table
     * @param string|null $connection
     * @return array
     */
    public static function getTableColumnsType(string $table, ?string $connection = null): array
    {
        $cacheKey = "auto_filter_table_columns_{$table}";
        
        // 尝试从缓存获取
        $cache = static::getCache();
        if ($cache && $cache->has($cacheKey)) {
            return $cache->get($cacheKey, []);
        }

        $result = static::fetchTableColumns($table, $connection);
        
        // 缓存结果
        if ($cache) {
            $cache->set($cacheKey, $result, 3600);
        }
        
        return $result;
    }

    /**
     * 获取表字段信息
     */
    protected static function fetchTableColumns(string $table, ?string $connection = null): array
    {
        try {
            $db = Db::connection($connection);

            // 处理跨库表名，如 "database.table"
            if (strpos($table, '.') !== false) {
                [$schema, $tableName] = explode('.', $table, 2);
                $columns = $db->select(
                    "SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.columns WHERE table_schema = ? AND table_name = ?",
                    [$schema, $tableName]
                );
            } else {
                $columns = $db->select(
                    "SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ?",
                    [$table]
                );
            }

            $map = [];
            foreach ($columns as $col) {
                $map[$col->COLUMN_NAME] = $col->DATA_TYPE;
            }

            return $map;
        } catch (\Exception $e) {
            // 如果数据库查询失败，返回空数组
            return [];
        }
    }

    /**
     * 获取缓存实例
     */
    protected static function getCache(): ?CacheInterface
    {
        try {
            $container = ApplicationContext::getContainer();
            return $container->get(CacheInterface::class);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取字段类型
     *
     * @param string $field
     * @param string $table
     * @param string|null $connection
     * @return string|null
     */
    public static function getFieldType(string $field, string $table, ?string $connection = null): ?string
    {
        $columnsMap = self::getTableColumnsType($table, $connection);

        return $columnsMap[$field] ?? null;
    }
}
