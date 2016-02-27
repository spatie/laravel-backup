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
    /** @var \Spatie\Backup\BackupDestination\Backup */
    protected $newestBackup;

    /**
     * @param \Spatie\Backup\BackupDestination\BackupCollection $backups
     */
    public function deleteOldBackups(BackupCollection $backups)
    {
        // Don't ever delete the newest backup.
        $this->newestBackup = $backups->shift();

        $dateRanges = $this->calculateDateRanges();

        $backupsPerPeriod = $dateRanges->map(function (Period $period) use ($backups) {
            return $backups->filter(function (Backup $backup) use ($period) {
               return $backup->date()->between($period->getStartDate(), $period->getEndDate());
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

    /**
     * @return \Illuminate\Support\Collection
     */
    protected function calculateDateRanges()
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

    /**
     * @param \Illuminate\Support\Collection $backups
     * @param string                         $dateFormat
     *
     * @return \Illuminate\Support\Collection
     */
    protected function groupByDateFormat(Collection $backups, $dateFormat)
    {
        return $backups->groupBy(function (Backup $backup) use ($dateFormat) {
            return $backup->date()->format($dateFormat);
        });
    }

    /**
     * @param \Illuminate\Support\Collection $backupsPerPeriod
     */
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

    /**
     * @param \Carbon\Carbon                                    $endDate
     * @param \Spatie\Backup\BackupDestination\BackupCollection $backups
     */
    protected function removeBackupsOlderThan(Carbon $endDate, BackupCollection $backups)
    {
        $backups->filter(function (Backup $backup) use ($endDate) {
            return $backup->exists() && $backup->date()->lt($endDate);
        })->each(function (Backup $backup) {
           $backup->delete();
        });
    }

    /**
     * @param \Spatie\Backup\BackupDestination\BackupCollection $backups
     */
    protected function removeOldestsBackupsUntilUsingMaximumStorage(BackupCollection $backups)
    {
        $maximumSize = $this->config->get('laravel-backup.cleanup.defaultStrategy.deleteOldestBackupsWhenUsingMoreMegabytesThan')
         * 1024 * 1024;

        if (!$oldestBackup = $backups->oldest()) {
            return;
        }

        if (($backups->size() + $this->newestBackup->size()) <= $maximumSize) {
            return;
        }

        $oldestBackup->delete();

        $this->removeOldestsBackupsUntilUsingMaximumStorage($backups);
    }
}
