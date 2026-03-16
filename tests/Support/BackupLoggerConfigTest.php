<?php

use Illuminate\Support\Facades\Log;
use Spatie\Backup\Support\BackupLogger;

it('logs to the default channel when log_channel is null', function () {
    config(['backup.log_channel' => null]);

    $this->app->forgetInstance(BackupLogger::class);

    Log::shouldReceive('log')->with('info', '[backup] test message')->once();

    app(BackupLogger::class)->info('test message');
});

it('logs to a specific channel when log_channel is a string', function () {
    config(['backup.log_channel' => 'single']);

    $this->app->forgetInstance(BackupLogger::class);

    Log::shouldReceive('channel')->with('single')->andReturn(
        $log = Mockery::mock(Psr\Log\LoggerInterface::class)
    );

    $log->shouldReceive('log')->with('info', '[backup] test message')->once();

    app(BackupLogger::class)->info('test message');
});

it('does not log when log_channel is false', function () {
    config(['backup.log_channel' => false]);

    $this->app->forgetInstance(BackupLogger::class);

    Log::shouldReceive('log')->never();
    Log::shouldReceive('channel')->never();

    app(BackupLogger::class)->info('test message');
});

it('logs to the default channel when log_channel config is missing', function () {
    $config = config('backup');
    unset($config['log_channel']);
    config(['backup' => $config]);

    $this->app->forgetInstance(BackupLogger::class);

    Log::shouldReceive('log')->with('info', '[backup] test message')->once();

    app(BackupLogger::class)->info('test message');
});
