<?php

declare(strict_types=1);

return [
    // 是否启用通用 SQL 同步能力。
    'enabled' => true,

    // 各业务项目保留自己的 SQL 配置文件。
    'sql_config_path' => BASE_PATH . '/config/sql.php',

    // 同步完成后是否通知中间件执行 SQL。
    'notify_general' => true,

    // 业务端 sql_handle_record 模型配置（OA 示例）。
    'store' => [
        'model_class' => \App\Model\SqlHandleRecord::class,
    ],

    // 中间件通知配置（OA 示例）。
    'notify' => [
        // 默认沿用业务项目 BASE_URL。
        'base_url' => env('BASE_URL', 'https://api.testfw.cn'),
        'path' => '/gn/public/handle_sql',
        'method' => 'POST',
        'payload_key' => 'database',
        'timeout' => 8.0,
        'headers' => [],
    ],
];
