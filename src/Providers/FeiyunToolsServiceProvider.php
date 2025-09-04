<?php

namespace Feiyun\Tools\Providers;

use Feiyun\Tools\AutoFilter\Providers\AutoFilterServiceProvider;
use Illuminate\Support\ServiceProvider;

class FeiyunToolsServiceProvider extends ServiceProvider
{
    /**
     * 所有工具的服务提供者
     *
     * @var array
     */
    protected $toolProviders = [
        AutoFilterServiceProvider::class,
        // 在这里添加更多工具的服务提供者
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // 注册所有工具的服务提供者
        foreach ($this->toolProviders as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // 这里可以添加全局的引导逻辑
    }
}
