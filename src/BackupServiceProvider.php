<?php

namespace Spatie\Backup;

use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/laravel-backup.php' => config_path('laravel-backup.php'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app['command.backup:run'] = $this->app->share(
            function ($app) {
                return new Commands\BackupCommand();
            }
        );

        $this->app['command.backup:clean'] = $this->app->share(
            function ($app) {
                return new Commands\CleanCommand();
            }
        );

        $this->commands(['command.backup:run', 'command.backup:clean']);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'command.backup:run',
            'command.backup:clean',
        ];
    }
}
