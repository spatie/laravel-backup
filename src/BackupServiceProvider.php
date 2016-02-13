<?php

namespace Spatie\Backup;

use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Commands\CleanupCommand;
use Spatie\Backup\Commands\MonitorCommand;
use Spatie\Backup\Commands\OverviewCommand;
use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\Backup\Notifications\HandlesBackupNotifications;

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
        $this->app->bind('command.backup:monitor', MonitorCommand::class);
        $this->app->bind('command.backup:overview', OverviewCommand::class);

        $this->commands([
            'command.backup:run',
            'command.backup:clean',
            'command.backup:monitor',
            'command.backup:overview',
        ]);

        $this->app->singleton(ConsoleOutput::class);
    }
}
