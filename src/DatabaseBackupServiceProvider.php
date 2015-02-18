<?php namespace Spatie\DatabaseBackup;

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
        $this->publishes([
            __DIR__.'/Assets/config/laravel-backup.php' => config_path('laravel-backup.php'),
        ]);

        $backupConfig = config('laravel-backup');

        $this->writeIgnoreFile($backupConfig['path']);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $databaseBuilder = new DatabaseBuilder();

        $this->app['command.db:backup'] = $this->app->share(
            function ($app) use ($databaseBuilder) {
                return new Commands\BackupCommand($databaseBuilder);
            }
        );

        $this->commands('command.db:backup');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['command.db:backup'];
    }

    /**
     * Copy the gitignore stub to the given directory
     *
     * @param $directory
     */
    public function writeIgnoreFile($directory)
    {
        $destinationFile = $directory.'/.gitignore';

        if(!file_exists($destinationFile))
        {
            $this->app['files']->copy(__DIR__.'/../stubs/gitignore.txt', $destinationFile);
        }
    }
}
