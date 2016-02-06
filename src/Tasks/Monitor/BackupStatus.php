<?php

namespace Spatie\Backup\Tasks\Monitor;

class BackupStatus
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $backupDestinationStatuses;

    public function __construct(array $monitorConfig)
    {
        $this->name = $monitorConfig['name'];

        $this->backupDestinationStatuses = BackupDestinationStatusFactory::createFromArray($monitorConfig);
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function newestBackupIsToolOld() : bool
    {
        foreach($this->backupDestinationStatuses as $backupDestinationStatus) {
            if ($backupDestinationStatus->newestBackupIsTooOld()) {
                return true;
            }
        }

        return false;
    }

    public function backupUsesTooMuchStorage() : bool
    {
        foreach($this->backupDestinationStatuses as $backupDestinationStatus) {
            if ($backupDestinationStatus->backupUsesTooMuchStorage()) {
                return true;
            }
        }

        return false;
    }
}