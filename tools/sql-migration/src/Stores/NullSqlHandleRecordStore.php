<?php

declare(strict_types=1);

namespace Feiyun\Tools\SqlMigration\Stores;

use Feiyun\Tools\SqlMigration\Contracts\SqlHandleRecordStoreInterface;

/**
 * 默认空存储实现。
 *
 * 仅用于占位，提醒业务方必须替换为真实实现。
 */
class NullSqlHandleRecordStore implements SqlHandleRecordStoreInterface
{
    public function existsBySerialNumber(string $serialNumber): bool
    {
        return false;
    }

    public function createPendingRecord(array $record): void
    {
    }

    public function getPendingDatabases(): array
    {
        return [];
    }
}
