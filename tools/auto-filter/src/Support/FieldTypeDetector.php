<?php

namespace Feiyun\Tools\AutoFilter\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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

        return Cache::remember($cacheKey, 3600, function () use ($table, $connection) {
            $db = $connection ? DB::connection($connection) : DB::connection();

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
        });
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
