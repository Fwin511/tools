<?php

declare(strict_types=1);

namespace Feiyun\Tools\Providers;

class FeiyunToolsServiceProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                // 全局依赖注册
            ],
            'commands' => [
                // 全局命令注册
            ],
            'listeners' => [
                // 全局监听器注册
            ],
            'publish' => [
                [
                    'id' => 'feiyun-tools-config',
                    'description' => 'Feiyun Tools configuration files.',
                    'source' => __DIR__ . '/../../tools/auto-filter/config/auto-filter.php',
                    'destination' => BASE_PATH . '/config/autoload/auto-filter.php',
                ],
            ],
        ];
    }
}
