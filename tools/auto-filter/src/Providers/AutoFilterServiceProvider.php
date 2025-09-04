<?php

namespace Feiyun\Tools\AutoFilter\Providers;

use Illuminate\Support\ServiceProvider;

class AutoFilterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/auto-filter.php',
            'auto-filter'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/auto-filter.php' => config_path('auto-filter.php'),
            ], 'feiyun-auto-filter-config');
        }
    }
}
