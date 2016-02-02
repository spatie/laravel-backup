<?php

namespace Spatie\Backup;

use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Commands\CleanupCommand;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-backup.php' => config_path('laravel-backup.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-backup.php', 'laravel-backup');

        $this->app->bind('command.backup:run', BackupCommand::class);
        $this->app->bind('command.backup:clean', CleanupCommand::class);

        $this->commands([
            'command.backup:run',
            'command.backup:clean',
        ]);
    }
}
