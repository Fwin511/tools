<?php

declare(strict_types=1);

namespace Feiyun\Tools\SqlMigration\Providers;

use Feiyun\Tools\SqlMigration\Contracts\SqlHandleRecordStoreInterface;
use Feiyun\Tools\SqlMigration\Contracts\SqlMigrationNotifierInterface;
use Feiyun\Tools\SqlMigration\Listeners\WorkerStartSqlSyncListener;
use Feiyun\Tools\SqlMigration\Notifiers\HttpSqlMigrationNotifier;
use Feiyun\Tools\SqlMigration\Stores\ModelSqlHandleRecordStore;

class SqlMigrationServiceProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                SqlHandleRecordStoreInterface::class => ModelSqlHandleRecordStore::class,
                SqlMigrationNotifierInterface::class => HttpSqlMigrationNotifier::class,
            ],
            'commands' => [],
            'listeners' => [
                WorkerStartSqlSyncListener::class,
            ],
            'publish' => [
                [
                    'id' => 'sql-migration-config',
                    'description' => 'SQL migration tool configuration file.',
                    'source' => __DIR__ . '/../../config/sql-migration.php',
                    'destination' => BASE_PATH . '/config/autoload/sql-migration.php',
                ],
            ],
        ];
    }
}
