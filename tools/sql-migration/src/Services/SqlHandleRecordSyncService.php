<?php

declare(strict_types=1);

namespace Feiyun\Tools\SqlMigration\Services;

use Feiyun\Tools\SqlMigration\Contracts\SqlHandleRecordStoreInterface;
use Feiyun\Tools\SqlMigration\Contracts\SqlMigrationNotifierInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Throwable;

class SqlHandleRecordSyncService
{
    private ConfigInterface $config;

    private SqlHandleRecordStoreInterface $store;

    private SqlMigrationNotifierInterface $notifier;

    private StdoutLoggerInterface $logger;

    public function __construct(
        ConfigInterface $config,
        SqlHandleRecordStoreInterface $store,
        SqlMigrationNotifierInterface $notifier,
        StdoutLoggerInterface $logger
    ) {
        $this->config = $config;
        $this->store = $store;
        $this->notifier = $notifier;
        $this->logger = $logger;
    }

    /**
     * 从业务端 config/sql.php 同步到 sql_handle_record，并可选通知中间件执行。
     *
     * @return array{total:int,inserted:int,skipped:int,failed:int,notified:bool}
     */
    public function syncAndNotify(bool $notifyGeneral = true): array
    {
        $summary = [
            'total' => 0,
            'inserted' => 0,
            'skipped' => 0,
            'failed' => 0,
            'notified' => false,
        ];

        $rows = $this->loadConfigRows();
        $summary['total'] = count($rows);
        if ($rows === []) {
            return $summary;
        }

        foreach ($rows as $row) {
            if (! is_array($row)) {
                $summary['failed']++;
                continue;
            }

            $serialNumber = trim((string) ($row['serial_number'] ?? ''));
            if ($serialNumber === '') {
                $summary['failed']++;
                continue;
            }

            try {
                if ($this->store->existsBySerialNumber($serialNumber)) {
                    $summary['skipped']++;
                    continue;
                }

                $this->store->createPendingRecord([
                    'is_run' => 0,
                    'db_name' => (string) ($row['db_name'] ?? ''),
                    'serial_number' => $serialNumber,
                    'title' => (string) ($row['title'] ?? ''),
                    'sql' => (string) ($row['sql'] ?? ''),
                    'created_at' => date('Y-m-d H:i:s'),
                    'result' => 0,
                ]);

                $summary['inserted']++;
            } catch (Throwable $throwable) {
                $summary['failed']++;
                $this->logger->error(sprintf(
                    '[feiyun-tools][sql-migration] sync failed serial_number=%s error=%s',
                    $serialNumber,
                    $throwable->getMessage()
                ));
            }
        }

        if (! $notifyGeneral) {
            return $summary;
        }

        try {
            $databases = array_values(array_filter(array_unique($this->store->getPendingDatabases())));
            if ($databases === []) {
                return $summary;
            }

            $summary['notified'] = $this->notifier->notifyDatabases($databases);
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf(
                '[feiyun-tools][sql-migration] notify prepare failed: %s',
                $throwable->getMessage()
            ));
        }

        return $summary;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadConfigRows(): array
    {
        $configPath = (string) $this->config->get('sql-migration.sql_config_path', BASE_PATH . '/config/sql.php');
        if ($configPath === '' || ! is_file($configPath)) {
            return [];
        }

        $rows = require $configPath;

        return is_array($rows) ? $rows : [];
    }
}
