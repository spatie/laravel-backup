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

    public function deleteOldBackups(BackupCollection $backups)
    {
        // Don't ever delete the newest backup.
        $this->newestBackup = $backups->shift();

        $dateRanges = $this->calculateDateRanges();

        $backupsPerPeriod = $dateRanges->map(function (Period $period) use ($backups) {
            return $backups->filter(function (Backup $backup) use ($period) {
                return $backup->date()->between($period->startDate(), $period->endDate());
            });
        });

        $backupsPerPeriod['daily'] = $this->groupByDateFormat($backupsPerPeriod['daily'], 'Ymd');
        $backupsPerPeriod['weekly'] = $this->groupByDateFormat($backupsPerPeriod['weekly'], 'YW');
        $backupsPerPeriod['monthly'] = $this->groupByDateFormat($backupsPerPeriod['monthly'], 'Ym');
        $backupsPerPeriod['yearly'] = $this->groupByDateFormat($backupsPerPeriod['yearly'], 'Y');

        $this->removeBackupsForAllPeriodsExceptOne($backupsPerPeriod);

        $this->removeBackupsOlderThan($dateRanges['yearly']->endDate(), $backups);

        $this->removeOldBackupsUntilUsingLessThanMaximumStorage($backups);
    }

    protected function calculateDateRanges(): Collection
    {
        $config = $this->config->get('backup.cleanup.default_strategy');

        $daily = new Period(
            Carbon::now()->subDays($config['keep_all_backups_for_days']),
            Carbon::now()
                ->subDays($config['keep_all_backups_for_days'])
                ->subDays($config['keep_daily_backups_for_days'])
        );

        $weekly = new Period(
            $daily->endDate(),
            $daily->endDate()
                ->subWeeks($config['keep_weekly_backups_for_weeks'])
        );

        $monthly = new Period(
            $weekly->endDate(),
            $weekly->endDate()
                ->subMonths($config['keep_monthly_backups_for_months'])
        );

        $yearly = new Period(
            $monthly->endDate(),
            $monthly->endDate()
                ->subYears($config['keep_yearly_backups_for_years'])
        );

        return collect(compact('daily', 'weekly', 'monthly', 'yearly'));
    }

    protected function groupByDateFormat(Collection $backups, string $dateFormat): Collection
    {
        return $backups->groupBy(function (Backup $backup) use ($dateFormat) {
            return $backup->date()->format($dateFormat);
        });
    }

    protected function removeBackupsForAllPeriodsExceptOne(Collection $backupsPerPeriod)
    {
        $backupsPerPeriod->each(function (Collection $groupedBackupsByDateProperty, string $periodName) {
            $groupedBackupsByDateProperty->each(function (Collection $group) {
                $group->shift();

                $group->each->delete();
            });
        });
    }

    protected function removeBackupsOlderThan(Carbon $endDate, BackupCollection $backups)
    {
        $backups->filter(function (Backup $backup) use ($endDate) {
            return $backup->exists() && $backup->date()->lt($endDate);
        })->each->delete();
    }

    protected function removeOldBackupsUntilUsingLessThanMaximumStorage(BackupCollection $backups)
    {
        if (! $oldest = $backups->oldest()) {
            return;
        }

        $maximumSize = $this->config->get('backup.cleanup.default_strategy.delete_oldest_backups_when_using_more_megabytes_than')
            * 1024 * 1024;

        if (($backups->size() + $this->newestBackup->size()) <= $maximumSize) {
            return;
        }
        $oldest->delete();

        $backups = $backups->filter->exists();

        $this->removeOldBackupsUntilUsingLessThanMaximumStorage($backups);
    }
}
