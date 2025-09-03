<?php

namespace Feiyun\AutoFilter\Support;

use Illuminate\Support\Arr;
use Carbon\Carbon;

class QueryBuilder
{
    /**
     * 根据字段类型构建 where 条件
     *
     * @param mixed $query
     * @param string $field
     * @param mixed $value
     * @param string $type
     * @return void
     */
    public static function buildWhere($query, string $field, $value, string $type): void
    {
        switch ($type) {
            case "char":
            case "varchar":
            case "varbinary":
            case "binary":
            case "tinytext":
            case "text":
            case "mediumtext":
            case "longtext":
            case "json":
                $query->where($field, 'like', "%{$value}%");
                break;

            case "tinyint":
            case "smallint":
            case "mediumint":
            case "int":
            case "bigint":
                $query->whereIn($field, Arr::wrap($value));
                break;

            case "decimal":
            case "float":
            case "double":
                if (is_array($value) && isset($value['start']) && isset($value['end'])) {
                    $query->whereBetween($field, [$value['start'], $value['end']]);
                }
                break;

            case "date":
                if (is_array($value) && isset($value['start_time']) && isset($value['end_time'])) {
                    $query->whereBetween($field, [$value['start_time'], $value['end_time']]);
                }
                break;

            case "datetime":
            case "timestamp":
                if (is_array($value) && isset($value['start_time']) && isset($value['end_time'])) {
                    $startTime = Carbon::parse($value['start_time'])->startOfDay()->toDateTimeString();
                    $endTime = Carbon::parse($value['end_time'])->endOfDay()->toDateTimeString();
                    $query->whereBetween($field, [$startTime, $endTime]);
                }
                break;
        }
    }

    /**
     * 检查字段是否被允许
     *
     * @param string $key
     * @param array $whitelist
     * @param array $blacklist
     * @return bool
     */
    public static function isFieldAllowed(string $key, array $whitelist, array $blacklist): bool
    {
        if (in_array($key, $blacklist)) {
            return false;
        }

        if (!empty($whitelist) && !in_array($key, $whitelist)) {
            return false;
        }

        return true;
    }
}
