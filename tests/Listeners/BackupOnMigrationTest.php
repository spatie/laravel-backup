<?php

use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Listeners\BackupOnMigration;
use Mockery as m;

it('does not backup when disabled in config', function () {
    config()->set('backup.backup_before_migration', false);
    
    $mock = m::mock(Kernel::class);
    $mock->shouldReceive('call')->never();
    $this->instance(Kernel::class, $mock);
    
    $listener = new BackupOnMigration();
    $listener->handle(new MigrationsStarted('up'));
});

it('does not backup when environment does not match', function () {
    config()->set('backup.backup_before_migration', true);
    config()->set('backup.backup_before_migration_environments', ['production']);
    $this->app->detectEnvironment(fn() => 'local');
    
    $mock = m::mock(Kernel::class);
    $mock->shouldReceive('call')->never();
    $this->instance(Kernel::class, $mock);
    
    $listener = new BackupOnMigration();
    $listener->handle(new MigrationsStarted('up'));
});

it('runs backup when enabled and environment matches', function () {
    config()->set('backup.backup_before_migration', true);
    config()->set('backup.backup_before_migration_environments', ['production']);
    $this->app->detectEnvironment(fn() => 'production');
    
    $mock = m::mock(Kernel::class);
    $mock->shouldReceive('call')
        ->once()
        ->with('backup:run', ['--only-db' => true]);
    $this->instance(Kernel::class, $mock);
        
    $listener = new BackupOnMigration();
    $listener->handle(new MigrationsStarted('up'));
});
