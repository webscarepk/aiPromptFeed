<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModelWorkerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\ModelWorkers\ModelWorkerRegistry::class, function ($app) {
            return new \App\Services\ModelWorkers\ModelWorkerRegistry();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
