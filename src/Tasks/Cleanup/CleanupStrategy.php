<?php

namespace Spatie\Backup\Tasks\Cleanup;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\Collection;

abstract class CleanupStrategy
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    abstract public function deleteOldBackups(Collection $backups);
}
