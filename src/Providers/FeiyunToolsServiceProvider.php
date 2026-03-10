<?php

declare(strict_types=1);

namespace Feiyun\Tools\Providers;

use Feiyun\Tools\SqlMigration\Contracts\SqlHandleRecordStoreInterface;
use Feiyun\Tools\SqlMigration\Contracts\SqlMigrationNotifierInterface;
use Feiyun\Tools\SqlMigration\Listeners\WorkerStartSqlSyncListener;
use Feiyun\Tools\SqlMigration\Notifiers\HttpSqlMigrationNotifier;
use Feiyun\Tools\SqlMigration\Stores\ModelSqlHandleRecordStore;

class FeiyunToolsServiceProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // SQL 迁移模块默认依赖，业务项目可按需覆盖绑定。
                SqlHandleRecordStoreInterface::class => ModelSqlHandleRecordStore::class,
                SqlMigrationNotifierInterface::class => HttpSqlMigrationNotifier::class,
            ],
            'commands' => [
                // 全局命令注册
            ],
            'listeners' => [
                // 统一监听器，受 sql-migration.enabled 控制。
                WorkerStartSqlSyncListener::class,
            ],
            'publish' => [
                [
                    'id' => 'feiyun-tools-auto-filter-config',
                    'description' => 'Auto Filter configuration file.',
                    'source' => __DIR__ . '/../../tools/auto-filter/config/auto-filter.php',
                    'destination' => BASE_PATH . '/config/autoload/auto-filter.php',
                ],
                [
                    'id' => 'feiyun-tools-sql-migration-config',
                    'description' => 'SQL migration runtime config file.',
                    'source' => __DIR__ . '/../../tools/sql-migration/config/sql-migration.php',
                    'destination' => BASE_PATH . '/config/autoload/sql-migration.php',
                ],
                [
                    'id' => 'feiyun-tools-sql-migration-sql-template',
                    'description' => 'SQL migration template file.',
                    'source' => __DIR__ . '/../../tools/sql-migration/config/sql.php',
                    'destination' => BASE_PATH . '/config/sql.php',
                ],
            ],
        ];
    }
}
