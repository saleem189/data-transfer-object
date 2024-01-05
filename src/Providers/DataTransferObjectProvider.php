<?php

namespace Saleem\DataTransferObject\Providers;

use Illuminate\Support\ServiceProvider;
use Saleem\DataTransferObject\Commands\MakeDto;

class DataTransferObjectProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish your stubs or other assets if needed
        $this->publishes([
            __DIR__.'/../Stubs' => base_path('stubs'),
        ], 'stubs');

        $this->publishes([
            __DIR__.'/../Config/data-transfer-object.php' => config_path('data-transfer-object.php'),
        ], 'config-file');
    }

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeDto::class,
            ]);
        }
        $this->mergeConfigFrom(
            __DIR__.'/../Config/data-transfer-object.php', 'data-transfer-object'
        );
    }
}
