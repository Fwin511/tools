<?php

declare(strict_types=1);

use function Hyperf\Support\env;

$dbName = env('DB_DATABASE', 'hyperf');

return [
    [
        'db_name' => $dbName,
        'serial_number' => 'replace-with-uuid-1', // 唯一标识，存在则不重复写入。
        'title' => '示例：创建测试表',
        'sql' => "CREATE TABLE `{$dbName}`.`sql_migration_demo` (\n  `id` int unsigned NOT NULL AUTO_INCREMENT,\n  PRIMARY KEY (`id`)\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SQL迁移示例表';",
    ],
    [
        'db_name' => $dbName,
        'serial_number' => 'replace-with-uuid-2', // 唯一标识，存在则不重复写入。
        'title' => '示例：新增字段',
        'sql' => "ALTER TABLE `{$dbName}`.`sql_migration_demo`\nADD COLUMN `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注' AFTER `id`;",
    ],
];
