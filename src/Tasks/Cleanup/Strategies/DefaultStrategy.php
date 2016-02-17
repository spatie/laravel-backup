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
    /** @var  \Spatie\Backup\BackupDestination\Backup */
    protected $newestBackup;

    public function deleteOldBackups(BackupCollection $backups)
    {
        //do not ever delete the newest backup
        $this->newestBackup = $backups->shift();

        $dateRanges = $this->calculateDateRanges();

        $backupsPerPeriod = $dateRanges->map(function (Period $period) use ($backups) {
            return $backups->filter(function (Backup $backup) use ($period) {
               return $backup->getDate()->between($period->getStartDate(), $period->getEndDate());
            });
        });

        $backupsPerPeriod['daily'] = $this->groupByDateFormat($backupsPerPeriod['daily'], 'Ymd');
        $backupsPerPeriod['weekly'] = $this->groupByDateFormat($backupsPerPeriod['weekly'], 'YW');
        $backupsPerPeriod['monthly'] = $this->groupByDateFormat($backupsPerPeriod['monthly'], 'Ym');
        $backupsPerPeriod['yearly'] = $this->groupByDateFormat($backupsPerPeriod['yearly'], 'Y');

        $this->removeBackupsForAllPeriodsExceptOne($backupsPerPeriod);

        $this->removeBackupsOlderThan($dateRanges['yearly']->getEndDate(), $backups);

        $this->removeOldestsBackupsUntilUsingMaximumStorage($backups);
    }

    protected function calculateDateRanges() : Collection
    {
        $config = $this->config->get('laravel-backup.cleanup.defaultStrategy');

        $daily = new Period(
            Carbon::now()->subDays($config['keepAllBackupsForDays']),
            Carbon::now()
                ->subDays($config['keepAllBackupsForDays'])
                ->subDays($config['keepDailyBackupsForDays'])
        );

        $weekly = new Period(
            $daily->getEndDate(),
            $daily->getEndDate()
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

        return collect(compact('daily', 'weekly', 'monthly', 'yearly'));
    }

    protected function groupByDateFormat(Collection $backups, string $dateFormat) : Collection
    {
        return $backups->groupBy(function (Backup $backup) use ($dateFormat) {
            return $backup->getDate()->format($dateFormat);
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

        if (($backups->getSize() + $this->newestBackup->getSize()) <= $maximumSize) {
            return;
        }

        $oldestBackup->delete();

        $this->removeOldestsBackupsUntilUsingMaximumStorage($backups);
    }
}
