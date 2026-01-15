<?php

use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Contracts\Console\Kernel;
use Spatie\Backup\Listeners\BackupOnMigration;

it('does not backup when disabled in config', function () {
    config()->set('backup.backup_before_migration', false);
    
    $this->mock(Kernel::class)
        ->shouldReceive('call')
        ->never();
    
    $listener = new BackupOnMigration();
    $listener->handle(new MigrationsStarted('up'));
});

it('does not backup when environment does not match', function () {
    config()->set('backup.backup_before_migration', true);
    config()->set('backup.backup_before_migration_environments', ['production']);
    $this->app->detectEnvironment(fn() => 'local');
    
    $this->mock(Kernel::class)
        ->shouldReceive('call')
        ->never();
    
    $listener = new BackupOnMigration();
    $listener->handle(new MigrationsStarted('up'));
});

it('runs backup when enabled and environment matches', function () {
    config()->set('backup.backup_before_migration', true);
    config()->set('backup.backup_before_migration_environments', ['production']);
    $this->app->detectEnvironment(fn() => 'production');
    
    $this->mock(Kernel::class)
        ->shouldReceive('call')
        ->once()
        ->with('backup:run', ['--only-db' => true]);
        
    $listener = new BackupOnMigration();
    $listener->handle(new MigrationsStarted('up'));
});
