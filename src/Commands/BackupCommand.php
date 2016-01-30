<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use InvalidCommand;

class BackupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:run {only-db?} {only-files?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the backup.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->guardAgainstInvalidOptions();

        $backupJob = BackupJob::create();

        if ($this->option('only-db')) {
            $backupJob->doNotIncludeAnyFiles();
        }

        if ($this->option('only-files')) {
            $backupJob->doNotIncludeDatabase();
        }

        $backupJob->run();

        BackupManager::removeBackupsOlderThan();
    }

    protected function guardAgainstInvalidOptions()
    {
        if ($this->option('only-db') && $this->option('only-files')) {
            throw InvalidCommand::create('cannot use only-db and only-files together');
        }
    }
}
