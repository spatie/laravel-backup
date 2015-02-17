<?php

namespace Spatie\DatabaseBackup;

use Illuminate\Support\ServiceProvider;

class DatabaseBackupServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['command.db.backup'] = $this->app->share(
            function ($app) {
                return new Commands\BackupCommand();
            }
        );

        $this->commands('command.db.backup');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.db.backup'];
    }
}