<?php

declare(strict_types=1);

namespace Feiyun\Tools\SqlMigration\Stores;

use Feiyun\Tools\SqlMigration\Contracts\SqlHandleRecordStoreInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Builder;
use RuntimeException;

/**
 * 基于业务项目模型类的默认存储实现。
 *
 * 通过配置 `sql-migration.store.*` 指定模型与字段映射，避免每个项目重复写 Store 适配器。
 */
class ModelSqlHandleRecordStore implements SqlHandleRecordStoreInterface
{
    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function existsBySerialNumber(string $serialNumber): bool
    {
        return $this->newQuery()
            ->where($this->field('serial_number_field', 'serial_number'), $serialNumber)
            ->exists();
    }

    public function createPendingRecord(array $record): void
    {
        $payload = [
            $this->field('is_run_field', 'is_run') => (int) ($record['is_run'] ?? 0),
            $this->field('db_name_field', 'db_name') => (string) ($record['db_name'] ?? ''),
            $this->field('serial_number_field', 'serial_number') => (string) ($record['serial_number'] ?? ''),
            $this->field('title_field', 'title') => (string) ($record['title'] ?? ''),
            $this->field('sql_field', 'sql') => (string) ($record['sql'] ?? ''),
            $this->field('created_at_field', 'created_at') => (string) ($record['created_at'] ?? date('Y-m-d H:i:s')),
            $this->field('result_field', 'result') => (int) ($record['result'] ?? 0),
        ];

        $this->newQuery()->create($payload);
    }

    public function getPendingDatabases(): array
    {
        $dbNameField = $this->field('db_name_field', 'db_name');

        return $this->newQuery()
            ->where($this->field('is_run_field', 'is_run'), 0)
            ->groupBy($dbNameField)
            ->pluck($dbNameField)
            ->toArray();
    }

    private function newQuery(): Builder
    {
        $modelClass = (string) $this->config->get('sql-migration.store.model_class', '');

        if ($modelClass === '' || ! class_exists($modelClass)) {
            throw new RuntimeException('sql-migration.store.model_class is not configured or class does not exist.');
        }

        if (! method_exists($modelClass, 'query')) {
            throw new RuntimeException(sprintf('Model class %s does not support static query().', $modelClass));
        }

        return $modelClass::query();
    }

    private function field(string $configKey, string $default): string
    {
        return (string) $this->config->get(sprintf('sql-migration.store.%s', $configKey), $default);
    }
}
