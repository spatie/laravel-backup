<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Events\CleanupHasFailed;
use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\Backup\Tasks\Cleanup\CleanupJob;

class CleanupCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all backups older than specified number of days in config.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ConsoleOutput::comment("Starting cleanup.");

        try {
            $config = config('laravel-backup');

            $backupDestinations = BackupDestinationFactory::createFromArray($config['backup']);

            $strategy = app($config['cleanup']['strategy']);

            $cleanupJob = new CleanupJob($backupDestinations, $strategy);

            $cleanupJob->run();

            ConsoleOutput::comment("Cleanup completed!");
        } catch (Exception $e) {
            event(new CleanupHasFailed());
        }
    }
}
