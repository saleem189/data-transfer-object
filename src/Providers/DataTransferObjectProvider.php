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

        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         MakeDto::class,
        //     ]);
        // }
    }

    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeDto::class,
            ]);
        }
    }
}
