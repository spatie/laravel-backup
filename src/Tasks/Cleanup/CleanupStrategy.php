<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Illuminate\Contracts\Config\Repository;
use Spatie\Backup\BackupDestination\BackupCollection;

abstract class CleanupStrategy
{
    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    abstract public function deleteOldBackups(BackupCollection $backups);
}
