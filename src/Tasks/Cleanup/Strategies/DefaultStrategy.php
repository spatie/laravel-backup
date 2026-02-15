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
        $strategy = $this->config->cleanup->defaultStrategy;

        $daily = new Period(
            Carbon::now()->subDays($strategy->keepAllBackupsForDays),
            Carbon::now()
                ->subDays($strategy->keepAllBackupsForDays)
                ->subDays($strategy->keepDailyBackupsForDays)
        );

        $weekly = new Period(
            $daily->endDate(),
            $daily->endDate()
                ->subWeeks($strategy->keepWeeklyBackupsForWeeks)
        );

        $monthly = new Period(
            $weekly->endDate(),
            $weekly->endDate()
                ->subMonths($strategy->keepMonthlyBackupsForMonths)
        );

        $yearly = new Period(
            $monthly->endDate(),
            $monthly->endDate()
                ->subYears($strategy->keepYearlyBackupsForYears)
        );

        return collect([
            'daily' => $daily,
            'weekly' => $weekly,
            'monthly' => $monthly,
            'yearly' => $yearly,
        ]);
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
        $maximumSize = $this->config->cleanup->defaultStrategy->deleteOldestBackupsWhenUsingMoreMegabytesThan;

        if ($maximumSize === null) {
            return;
        }

        while ($this->shouldRemoveOldestBackup($backups, $maximumSize)) {
            $backups->oldest()->delete();

            $backups = $backups->filter(fn (Backup $backup) => $backup->exists());
        }
    }

    protected function shouldRemoveOldestBackup(BackupCollection $backups, int $maximumSizeInMB): bool
    {
        if (! $backups->oldest()) {
            return false;
        }

        return ($backups->size() + $this->newestBackup->sizeInBytes()) > $maximumSizeInMB * 1024 * 1024;
    }
}
