<?php

namespace Spatie\Backup\Tasks\Monitor\HealthChecks;

use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\HealthCheck;
use Spatie\Backup\BackupDestination\BackupDestination;

class IncreasingFileSize extends HealthCheck
{
    protected $tolerance;

    public function __construct($tolerance = 0.05)
    {
        $this->tolerance = $this->parseValue($tolerance);
    }

    public function checkHealth(BackupDestination $backupDestination)
    {
        if ($backupDestination->backups()->count() < 2) {
            return;
        }

        list($newestSize, $previousSize) = [
            $backupDestination->backups()->get(0)->size(),
            $backupDestination->backups()->get(1)->size(),
        ];

        $relativeSize = $newestSize / $previousSize;
        $loss = 1 - $relativeSize;

        $this->failIf($loss > $this->tolerance, trans('backup::notifications.unhealthy_backup_found_size_reduction', [
            'from_size' => Format::humanReadableSize($previousSize),
            'to_size' => Format::humanReadableSize($newestSize),
            'percentage' => '-'.number_format($loss * 100, 2).'%',
        ]));
    }

    /**
     * @param $value
     * @return float|int
     */
    protected function parseValue($value)
    {
        if (! is_numeric($value) && preg_match('/%/', $value)) {
            return floatval($value) / 100;
        }

        return floatval($value);
    }
}
