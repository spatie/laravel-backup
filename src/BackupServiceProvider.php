<?php

namespace Spatie\Backup;

use Illuminate\Support\Facades\Event;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Commands\CleanupCommand;
use Spatie\Backup\Commands\ListCommand;
use Spatie\Backup\Commands\MonitorCommand;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\Backup\Listeners\EncryptBackupArchive;
use Spatie\Backup\Notifications\EventHandler;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BackupServiceProvider extends PackageServiceProvider
{

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-backup')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasCommands([
                BackupCommand::class,
                CleanupCommand::class,
                ListCommand::class,
                MonitorCommand::class,
            ]);
    }

    public function packageBooted()
    {
        if (EncryptBackupArchive::shouldEncrypt()) {
            Event::listen(BackupZipWasCreated::class, EncryptBackupArchive::class);
            $this->registerTranslations();
        }
    }

    public function packageRegistered()
    {
        $this->app['events']->subscribe(EventHandler::class);

        $this->app->singleton(ConsoleOutput::class);

        $this->app->bind(CleanupStrategy::class, config('backup.cleanup.strategy'));
    }

    protected function registerTranslations()
    {
        $currentLocale = app()->getLocale();

        $this->loadJSONTranslationsFrom(__DIR__.'/../resources/lang');
    }
}
