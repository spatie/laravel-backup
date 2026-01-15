<?php

namespace Spatie\Backup\Listeners;

use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Support\Facades\Artisan;

class BackupOnMigration
{
    public function handle(MigrationsStarted $event)
    {
        if (! config('backup.backup_before_migration')) {
            return;
        }

        $environments = config('backup.backup_before_migration_environments', []);
        
        if (! in_array(app()->environment(), $environments)) {
            return;
        }

        Artisan::call('backup:run', ['--only-db' => true]);
    }
}
