<?php

namespace Spatie\Backup;

use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-backup.php.php' => config_path('laravel-backup.php'),
        ], 'config');


    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-backup.php', 'laravel-backup');

        $this->app->bind(BackupJob::class, function() {

            return Backub

        });
    }
}
