<?php

namespace Feiyun\Tools\AutoFilter\Providers;

use Hyperf\Contract\ConfigInterface;

class AutoFilterServiceProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // 这里可以注册依赖
            ],
            'commands' => [
                // 这里可以注册命令
            ],
            'listeners' => [
                // 这里可以注册监听器
            ],
            'publish' => [
                [
                    'id' => 'auto-filter-config',
                    'description' => 'Auto Filter configuration file.',
                    'source' => __DIR__ . '/../../config/auto-filter.php',
                    'destination' => BASE_PATH . '/config/autoload/auto-filter.php',
                ],
            ],
        ];
    }
}
