<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\Backup\Helpers\Format;

class CleanupJob
{
    /** @var \Illuminate\Support\Collection */
    protected $backupDestinations;

    /** @var \Spatie\Backup\Tasks\Cleanup\Strategies\CleanupStrategy */
    protected $strategy;

    public function __construct(Collection $backupDestinations, CleanupStrategy $strategy)
    {
        $this->backupDestinations = $backupDestinations;
        $this->strategy = $strategy;
    }

    public function run()
    {
        $this->backupDestinations->each(function (BackupDestination $backupDestination) {

            consoleOutput()->info("Cleaning backups of {$backupDestination->getBackupName()} on {$backupDestination->getFilesystemType()}-filesystem");

            $this->strategy->deleteOldBackups($backupDestination->getBackups());
            event(new CleanupWasSuccessFul($backupDestination));

            $usedStorage = Format::getHumanReadableSize($backupDestination->getUsedStorage());
            consoleOutput()->info("Used storage after cleanup: {$usedStorage}");
        });
    }
}
