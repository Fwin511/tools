<?php

declare(strict_types=1);

namespace Feiyun\Tools\SqlMigration\Notifiers;

use Feiyun\Tools\SqlMigration\Contracts\SqlMigrationNotifierInterface;
use GuzzleHttp\Client;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Throwable;

/**
 * 基于 HTTP 的默认通知实现。
 *
 * 默认向中间件 `/gn/public/handle_sql` 发送 `{ "database": [...] }`。
 */
class HttpSqlMigrationNotifier implements SqlMigrationNotifierInterface
{
    private ConfigInterface $config;

    private StdoutLoggerInterface $logger;

    public function __construct(ConfigInterface $config, StdoutLoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function notifyDatabases(array $databases): bool
    {
        if ($databases === []) {
            return true;
        }

        try {
            $baseUrl = (string) $this->config->get('sql-migration.notify.base_url', $this->config->get('base_url', ''));
            $path = (string) $this->config->get('sql-migration.notify.path', '/gn/public/handle_sql');
            $method = strtoupper((string) $this->config->get('sql-migration.notify.method', 'POST'));
            $payloadKey = (string) $this->config->get('sql-migration.notify.payload_key', 'database');
            $timeout = (float) $this->config->get('sql-migration.notify.timeout', 8.0);
            $headers = (array) $this->config->get('sql-migration.notify.headers', []);

            if ($baseUrl === '') {
                $this->logger->error('[feiyun-tools][sql-migration] notify base_url is empty.');
                return false;
            }

            $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
            $client = new Client([
                'timeout' => $timeout,
            ]);

            $response = $client->request($method, $url, [
                'headers' => $headers,
                'json' => [
                    $payloadKey => array_values(array_unique($databases)),
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                return false;
            }

            $json = json_decode((string) $response->getBody(), true);
            if (! is_array($json)) {
                return true;
            }

            // 约定：返回结构存在 code 时，code=200 才视为成功。
            if (array_key_exists('code', $json)) {
                return (int) $json['code'] === 200;
            }

            return true;
        } catch (Throwable $throwable) {
            $this->logger->error(sprintf(
                '[feiyun-tools][sql-migration] notify failed: %s',
                $throwable->getMessage()
            ));

            return false;
        }
    }
}
