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
    protected ?Backup $newestBackup = null;

    public function deleteOldBackups(BackupCollection $backups): void
    {
        // Don't ever delete the newest backup.
        $this->newestBackup = $backups->shift();

        $dateRanges = $this->calculateDateRanges();

        /** @var Collection<string, BackupCollection> $backupsPerPeriod */
        $backupsPerPeriod = $dateRanges->map(function (Period $period) use ($backups) {
            return $backups
                ->filter(fn (Backup $backup) => $backup->date()->between($period->startDate(), $period->endDate()));
        });

        $backupsPerPeriod['daily'] = $this->groupByDateFormat($backupsPerPeriod['daily'], 'Ymd');
        $backupsPerPeriod['weekly'] = $this->groupByDateFormat($backupsPerPeriod['weekly'], 'YW');
        $backupsPerPeriod['monthly'] = $this->groupByDateFormat($backupsPerPeriod['monthly'], 'Ym');
        $backupsPerPeriod['yearly'] = $this->groupByDateFormat($backupsPerPeriod['yearly'], 'Y');

        $this->removeBackupsForAllPeriodsExceptOne($backupsPerPeriod);

        $this->removeBackupsOlderThan($dateRanges['yearly']->endDate(), $backups);

        $this->removeOldBackupsUntilUsingLessThanMaximumStorage($backups);
    }

    /** @return Collection<string, Period> */
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

    protected function groupByDateFormat(BackupCollection $backups, string $dateFormat): BackupCollection
    {
        return $backups->groupBy(fn (Backup $backup) => $backup->date()->format($dateFormat));
    }

    /** @param Collection<string, BackupCollection> $backupsPerPeriod */
    protected function removeBackupsForAllPeriodsExceptOne(Collection $backupsPerPeriod): void
    {
        $backupsPerPeriod->each(function (Collection $groupedBackupsByDateProperty, string $periodName) {
            $groupedBackupsByDateProperty->each(function (BackupCollection $group) {
                $group->shift();

                $group->each(fn (Backup $backup) => $backup->delete());
            });
        });
    }

    protected function removeBackupsOlderThan(Carbon $endDate, BackupCollection $backups): void
    {
        $backups
            ->filter(fn (Backup $backup) => $backup->exists() && $backup->date()->lt($endDate))
            ->each(fn (Backup $backup) => $backup->delete());
    }

    protected function removeOldBackupsUntilUsingLessThanMaximumStorage(BackupCollection $backups): void
    {
        if (! $this->shouldRemoveOldestBackup($backups)) {
            return;
        }

        $backups->oldest()->delete();

        $backups = $backups->filter(fn (Backup $backup) => $backup->exists());

        $this->removeOldBackupsUntilUsingLessThanMaximumStorage($backups);
    }

    protected function shouldRemoveOldestBackup(BackupCollection $backups): bool
    {
        if (! $backups->oldest()) {
            return false;
        }

        $maximumSize = $this->config->get('backup.cleanup.default_strategy.delete_oldest_backups_when_using_more_megabytes_than');

        if ($maximumSize === null) {
            return false;
        }

        if (($backups->size() + $this->newestBackup->sizeInBytes()) <= $maximumSize * 1024 * 1024) {
            return false;
        }

        return true;
    }
}
