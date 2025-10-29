<?php

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Exceptions\BackupFailed;
use Spatie\Backup\Tasks\Backup\FailoverManager;

beforeEach(function () {
    $this->config = Config::fromArray(config('backup'));
    $this->failoverManager = new FailoverManager($this->config);
});

it('can attempt failover when enabled', function () {
    config(['backup.backup.destination.enable_failover' => true]);
    config(['backup.backup.destination.fallback_disks' => ['s3']]);
    config(['backup.backup.destination.failover_retries' => 1]);

    $this->config = Config::fromArray(config('backup'));
    $this->failoverManager = new FailoverManager($this->config);

    $failedDestination = BackupDestination::create('local', 'test-app');
    $fallbackDestination = BackupDestination::create('s3', 'test-app');

    $this->failoverManager->setFallbackDestinations(collect([$fallbackDestination]));

    expect($this->config->backup->destination->enableFailover)->toBeTrue();
});

it('throws exception when failover is disabled', function () {
    config(['backup.backup.destination.enable_failover' => false]);

    $this->config = Config::fromArray(config('backup'));
    $this->failoverManager = new FailoverManager($this->config);

    $failedDestination = BackupDestination::create('local', 'test-app');
    $originalException = new Exception('Disk failed');

    expect(fn () => $this->failoverManager->attemptFailover($failedDestination, '/path/to/backup.zip', $originalException))
        ->toThrow(BackupFailed::class);
});

it('throws exception when no fallback destinations are available', function () {
    config(['backup.backup.destination.enable_failover' => true]);

    $this->config = Config::fromArray(config('backup'));
    $this->failoverManager = new FailoverManager($this->config);
    $this->failoverManager->setFallbackDestinations(new Collection);

    $failedDestination = BackupDestination::create('local', 'test-app');
    $originalException = new Exception('Disk failed');

    expect(fn () => $this->failoverManager->attemptFailover($failedDestination, '/path/to/backup.zip', $originalException))
        ->toThrow(BackupFailed::class);
});


it('respects failover retry configuration', function () {
    config(['backup.backup.destination.enable_failover' => true]);
    config(['backup.backup.destination.failover_retries' => 3]);
    config(['backup.backup.destination.failover_delay' => 1]);

    $this->config = Config::fromArray(config('backup'));
    $this->failoverManager = new FailoverManager($this->config);

    expect($this->config->backup->destination->failoverRetries)->toBe(3);
    expect($this->config->backup->destination->failoverDelay)->toBe(1);
});
