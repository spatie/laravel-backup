<?php

namespace Spatie\Backup\Tasks\Cleanup\Strategies;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupCollection;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;
use Spatie\Backup\Tasks\Cleanup\Period;

class DefaultStrategy extends CleanupStrategy
{
    public function deleteOldBackups(BackupCollection $backups)
    {
        //do not ever delete the youngest backup
        $backups->shift();

        $dateRanges = $this->calculateDateRanges();

        $backupsPerPeriod = $dateRanges->map(function (Period $period) use ($backups) {
            return $backups->filter(function (Backup $backup) use ($period) {
               return $backup->getDate()->between($period->getStartDate(), $period->getEndDate());
            });
        });

        $backupsPerPeriod['weekly'] = $this->groupByDateProperty($backupsPerPeriod['weekly'], 'weekOfYear');
        $backupsPerPeriod['monthly'] = $this->groupByDateProperty($backupsPerPeriod['monthly'], 'month');
        $backupsPerPeriod['yearly'] = $this->groupByDateProperty($backupsPerPeriod['yearly'], 'year');

        $this->removeBackupsForAllPeriodsExceptOne($backupsPerPeriod);

        $this->removeBackupsOlderThan($dateRanges['yearly']->getEndDate(), $backups);

        $this->removeOldestsBackupsUntilUsingMaximumStorage($backups);
    }

    protected function calculateDateRanges() : Collection
    {
        $config = $this->config->get('laravel-backup.cleanup.defaultStrategy');

        $weekly = new Period(
            Carbon::now()->subDays($config['keepDailyBackupsForDays']),
            Carbon::now()
                ->subDays($config['keepDailyBackupsForDays'])
                ->subWeeks($config['keepWeeklyBackupsForWeeks'])
        );

        $monthly = new Period(
            $weekly->getEndDate(),
            $weekly->getEndDate()
                ->subMonths($config['keepMonthlyBackupsForMonths'])
        );

        $yearly = new Period(
            $monthly->getEndDate(),
            $monthly->getEndDate()
                ->subYears($config['keepYearlyBackupsForYears'])
        );

        return collect(compact('weekly', 'monthly', 'yearly'));
    }

    protected function groupByDateProperty(Collection $backups, string $functionName) : Collection
    {
        return $backups->groupBy(function (Backup $backup) use ($functionName) {
            return $backup->getDate()->{$functionName};
        });
    }

    protected function removeBackupsForAllPeriodsExceptOne($backupsPerPeriod)
    {
        foreach ($backupsPerPeriod as $periodName => $groupedBackupsByDateProperty) {
            $groupedBackupsByDateProperty->each(function (Collection $group) {
                $group->shift();

                $group->each(function (Backup $backup) {
                    $backup->delete();
                });
            });
        }
    }

    protected function removeBackupsOlderThan(Carbon $endDate, BackupCollection $backups)
    {
        $backups->filter(function (Backup $backup) use ($endDate) {
            return $backup->exists() && $backup->getDate()->lt($endDate);
        })->each(function (Backup $backup) {
           $backup->delete();
        });
    }

    protected function removeOldestsBackupsUntilUsingMaximumStorage(BackupCollection $backups)
    {
        $maximumSize = $this->config->get('laravel-backup.cleanup.defaultStrategy.deleteOldestBackupsWhenUsingMoreMegabytesThan')
         * 1024 * 1024;

        if (!$oldestBackup = $backups->getOldestBackup()) {
            return;
        }

        if ($backups->getSize() <= $maximumSize ) {
            return;
        }

        $oldestBackup->delete();

        $this->removeOldestsBackupsUntilUsingMaximumStorage($backups);
    }
}
