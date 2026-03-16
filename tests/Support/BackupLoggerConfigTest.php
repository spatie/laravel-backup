<?php

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spatie\Backup\Support\BackupLogger;

it('uses the default log channel when logging.channel is null', function () {
    config(['backup.logging.channel' => null]);

    $this->app->forgetInstance(BackupLogger::class);
    $logger = $this->app->make(BackupLogger::class);

    expect(new ReflectionProperty($logger, 'logger'))
        ->getValue($logger)
        ->toBeNull();
});

it('uses a specific log channel when logging.channel is a string', function () {
    config(['backup.logging.channel' => 'single']);

    $this->app->forgetInstance(BackupLogger::class);
    $logger = $this->app->make(BackupLogger::class);

    expect(new ReflectionProperty($logger, 'logger'))
        ->getValue($logger)
        ->toBeInstanceOf(LoggerInterface::class);
});

it('disables logging entirely when logging.channel is false', function () {
    config(['backup.logging.channel' => false]);

    $this->app->forgetInstance(BackupLogger::class);
    $logger = $this->app->make(BackupLogger::class);

    expect(new ReflectionProperty($logger, 'logger'))
        ->getValue($logger)
        ->toBeInstanceOf(NullLogger::class);
});

it('preserves default behavior when logging config key is missing', function () {
    $config = config('backup');
    unset($config['logging']);
    config(['backup' => $config]);

    $this->app->forgetInstance(BackupLogger::class);
    $logger = $this->app->make(BackupLogger::class);

    expect(new ReflectionProperty($logger, 'logger'))
        ->getValue($logger)
        ->toBeNull();
});
