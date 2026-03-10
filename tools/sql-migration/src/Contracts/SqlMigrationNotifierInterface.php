<?php

declare(strict_types=1);

namespace Feiyun\Tools\SqlMigration\Contracts;

/**
 * SQL 执行通知器接口。
 *
 * 由业务项目实现，负责将待执行数据库列表通知到中间件。
 */
interface SqlMigrationNotifierInterface
{
    /**
     * @param string[] $databases
     */
    public function notifyDatabases(array $databases): bool;
}
