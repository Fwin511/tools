<?php

declare(strict_types=1);

namespace Feiyun\Tools\SqlMigration\Listeners;

use Feiyun\Tools\SqlMigration\Services\SqlHandleRecordSyncService;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Server\Listener\AfterWorkerStartListener;
use Throwable;

/**
 * 通用服务启动监听器。
 *
 * 使用方式：
 * - 业务端在 listeners.php 注册本监听器。
 * - 并在 dependencies.php 绑定两个适配接口实现。
 */
class WorkerStartSqlSyncListener extends AfterWorkerStartListener
{
    private ConfigInterface $config;

    private SqlHandleRecordSyncService $syncService;

    private StdoutLoggerInterface $logger;

    private bool $synced = false;

    public function __construct(
        StdoutLoggerInterface $logger,
        ConfigInterface $config,
        SqlHandleRecordSyncService $syncService
    ) {
        parent::__construct($logger);
        $this->logger = $logger;
        $this->config = $config;
        $this->syncService = $syncService;
    }

    public function process(object $event): void
    {
        parent::process($event);

        if ($this->synced || ! $this->config->get('sql-migration.enabled', false) || ! $this->shouldRun($event)) {
            return;
        }

        $this->synced = true;

        try {
            $notify = (bool) $this->config->get('sql-migration.notify_general', true);
            $summary = $this->syncService->syncAndNotify($notify);
            $this->logger->info(sprintf(
                '[feiyun-tools][sql-migration] total=%d inserted=%d skipped=%d failed=%d notified=%s',
                $summary['total'],
                $summary['inserted'],
                $summary['skipped'],
                $summary['failed'],
                $summary['notified'] ? '1' : '0'
            ));
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf(
                '[feiyun-tools][sql-migration] listener failed: %s',
                $throwable->getMessage()
            ));
        }
    }

    private function shouldRun(object $event): bool
    {
        if ($event instanceof MainCoroutineServerStart) {
            return true;
        }

        return $event instanceof AfterWorkerStart && $event->workerId === 0;
    }
}
