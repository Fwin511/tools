<?php

declare(strict_types=1);

namespace Feiyun\Tools\SqlMigration\Notifiers;

use Feiyun\Tools\SqlMigration\Contracts\SqlMigrationNotifierInterface;

/**
 * 默认空通知实现。
 *
 * 仅用于占位，提醒业务方必须替换为真实实现。
 */
class NullSqlMigrationNotifier implements SqlMigrationNotifierInterface
{
    public function notifyDatabases(array $databases): bool
    {
        return false;
    }
}
