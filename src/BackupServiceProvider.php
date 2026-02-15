<?php

namespace Spatie\Backup;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Commands\CleanupCommand;
use Spatie\Backup\Commands\ListCommand;
use Spatie\Backup\Commands\MonitorCommand;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Listeners\EncryptBackupArchive;
use Spatie\Backup\Notifications\Channels\Discord\DiscordChannel;
use Spatie\Backup\Notifications\Channels\Webhook\WebhookChannel;
use Spatie\Backup\Notifications\EventHandler;
use Spatie\Backup\Support\BackupLogger;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;

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

    public function packageBooted(): void
    {
        $this->app['events']->subscribe(EventHandler::class);

        Event::listen(BackupZipWasCreated::class, EncryptBackupArchive::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(BackupLogger::class);

        $this->app->bind(CleanupStrategy::class, config('backup.cleanup.strategy'));
        $this->app->bind('backup-temporary-project', fn () => new TemporaryDirectory(config('backup.backup.temporary_directory') ?? storage_path('app/backup-temp')));

        $this->registerNotificationChannels();

        $this->app->scoped(Config::class, function (): Config {
            return Config::fromArray(config('backup'));
        });
    }

    protected function registerNotificationChannels(): void
    {
        Notification::resolved(function (ChannelManager $service) {
            $service->extend('discord', function ($app): DiscordChannel {
                return new DiscordChannel;
            });

            $service->extend('webhook', function ($app): WebhookChannel {
                return new WebhookChannel;
            });
        });
    }
}
