# SQL Migration Tool

通用 SQL 迁移登记工具，适用于多业务端统一处理：

1. 读取各业务项目自己的 `config/sql.php`
2. 幂等写入业务端 `sql_handle_record`
3. 通知中间件按数据库列表执行

## 安装

```bash
composer require feiyun/tools
```

## 最少接入（推荐）

只需要配置，不需要写业务适配代码：

1. 发布配置

```bash
php bin/hyperf.php vendor:publish feiyun/tools
```

2. 配置 `config/autoload/sql-migration.php`

```php
return [
    'enabled' => true,
    'sql_config_path' => BASE_PATH . '/config/sql.php',
    'notify_general' => true,
    'store' => [
        'model_class' => App\\Model\\SqlHandleRecord::class,
    ],
    'notify' => [
        'path' => '/gn/public/handle_sql',
    ],
];
```

3. 保留每个业务项目自己的 `config/sql.php`

> 监听器 `WorkerStartSqlSyncListener` 已由主 Provider 自动注册，`enabled=false` 时不会执行。

## 可选高级接入

如果默认模型存储/HTTP通知不满足需求，可自定义并覆盖绑定：

- `Feiyun\\Tools\\SqlMigration\\Contracts\\SqlHandleRecordStoreInterface`
- `Feiyun\\Tools\\SqlMigration\\Contracts\\SqlMigrationNotifierInterface`

## 手动触发

业务项目可在控制器中注入：

```php
use Feiyun\\Tools\\SqlMigration\\Services\\SqlHandleRecordSyncService;

$summary = $service->syncAndNotify(true);
```
