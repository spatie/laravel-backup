<?php

namespace Spatie\Backup;

use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Commands\ListCommand;
use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Commands\CleanupCommand;
use Spatie\Backup\Commands\MonitorCommand;

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

        $this->handleDeprecatedConfigValues();

        $this->app['events']->subscribe(\Spatie\Backup\Notifications\EventHandler::class);

        $this->app->bind('command.backup:run', BackupCommand::class);
        $this->app->bind('command.backup:clean', CleanupCommand::class);
        $this->app->bind('command.backup:list', ListCommand::class);
        $this->app->bind('command.backup:monitor', MonitorCommand::class);

        $this->commands([
            'command.backup:run',
            'command.backup:clean',
            'command.backup:list',
            'command.backup:monitor',
        ]);

        $this->app->singleton(ConsoleOutput::class);
    }

    protected function handleDeprecatedConfigValues()
    {
        $renamedConfigValues = [

            /*
             * Earlier versions of the package used filesystems instead of disks
             */
            [
                'oldName' => 'laravel-backup.backup.destination.filesystems',
                'newName' => 'laravel-backup.backup.destination.disks',
            ],

            [
                'oldName' => 'laravel-backup.monitorBackups.filesystems',
                'newName' => 'laravel-backup.monitorBackups.disks',
            ],

            /*
             * Earlier versions of the package had a typo in the config value name
             */
            [
                'oldName' => 'laravel-backup.notifications.whenUnHealthyBackupWasFound',
                'newName' => 'laravel-backup.notifications.whenUnhealthyBackupWasFound',
            ],
        ];

        foreach ($renamedConfigValues as $renamedConfigValue) {
            if (config($renamedConfigValue['oldName'])) {
                config([$renamedConfigValue['newName'] => config($renamedConfigValue['oldName'])]);
            }
        }
    }
}
