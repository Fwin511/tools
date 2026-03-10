<?php

declare(strict_types=1);

return [
    // 是否启用通用 SQL 同步能力。
    'enabled' => false,

    // 业务端 SQL 配置文件路径（通常保持默认）。
    'sql_config_path' => BASE_PATH . '/config/sql.php',

    // 同步完成后是否通知中间件执行。
    'notify_general' => true,

    // 业务端记录存储模型映射。
    'store' => [
        // 业务项目 sql_handle_record 模型类，例如：App\Model\SqlHandleRecord::class
        'model_class' => '',

        // 字段映射（默认约定字段名一致时无需改动）。
        'serial_number_field' => 'serial_number',
        'db_name_field' => 'db_name',
        'is_run_field' => 'is_run',
        'title_field' => 'title',
        'sql_field' => 'sql',
        'created_at_field' => 'created_at',
        'result_field' => 'result',
    ],

    // 中间件通知配置。
    'notify' => [
        // 为空时默认使用业务项目 config('base_url')。
        'base_url' => '',
        'path' => '/gn/public/handle_sql',
        'method' => 'POST',
        'payload_key' => 'database',
        'timeout' => 8.0,
        'headers' => [],
    ],
];
