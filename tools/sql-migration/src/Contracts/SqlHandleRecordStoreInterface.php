<?php

declare(strict_types=1);

namespace Feiyun\Tools\SqlMigration\Contracts;

/**
 * 业务端 sql_handle_record 存储适配接口。
 *
 * 由业务项目实现，负责：
 * - 判断 serial_number 是否已存在
 * - 新增待执行记录
 * - 提供待执行记录所在数据库列表
 */
interface SqlHandleRecordStoreInterface
{
    public function existsBySerialNumber(string $serialNumber): bool;

    /**
     * @param array<string, mixed> $record
     */
    public function createPendingRecord(array $record): void;

    /**
     * @return string[]
     */
    public function getPendingDatabases(): array;
}
