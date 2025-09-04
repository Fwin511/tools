<?php

namespace Feiyun\Tools\AutoFilter\Support;


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
                $values = is_array($value) ? $value : [$value];
                $query->whereIn($field, $values);
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
                    $startTime = static::parseDateTime($value['start_time'], true);
                    $endTime = static::parseDateTime($value['end_time'], false);
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

    /**
     * 解析日期时间
     */
    protected static function parseDateTime(string $dateTime, bool $startOfDay = true): string
    {
        // 尝试使用 Carbon（如果可用）
        if (class_exists('\Carbon\Carbon')) {
            $carbon = \Carbon\Carbon::parse($dateTime);
            return $startOfDay ? $carbon->startOfDay()->toDateTimeString() : $carbon->endOfDay()->toDateTimeString();
        }

        // 使用原生 DateTime
        $dt = new \DateTime($dateTime);
        if ($startOfDay) {
            $dt->setTime(0, 0, 0);
        } else {
            $dt->setTime(23, 59, 59);
        }

        return $dt->format('Y-m-d H:i:s');
    }
}
