<?php

namespace Feiyun\Tools;

class ToolsManager
{
    /**
     * 获取所有可用的工具列表
     *
     * @return array
     */
    public static function getAvailableTools(): array
    {
        return [
            'auto-filter' => [
                'name' => '自动筛选',
                'description' => 'Laravel/Hyperf 模型自动筛选扩展，根据请求参数和字段类型自动构建查询条件',
                'namespace' => 'Feiyun\\Tools\\AutoFilter',
                'config' => 'auto-filter',
                'provider' => 'Feiyun\\Tools\\AutoFilter\\Providers\\AutoFilterServiceProvider',
                'status' => 'stable',
            ],
            // 在这里添加更多工具
        ];
    }

    /**
     * 检查工具是否可用
     *
     * @param string $tool
     * @return bool
     */
    public static function isToolAvailable(string $tool): bool
    {
        return array_key_exists($tool, self::getAvailableTools());
    }

    /**
     * 获取工具信息
     *
     * @param string $tool
     * @return array|null
     */
    public static function getToolInfo(string $tool): ?array
    {
        $tools = self::getAvailableTools();
        return $tools[$tool] ?? null;
    }
}
