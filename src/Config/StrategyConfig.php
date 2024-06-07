<?php

namespace Spatie\Backup\Config;

class StrategyConfig
{
    protected function __construct(
        public int $keepAllBackupsForDays,
        public int $keepDailyBackupsForDays,
        public int $keepWeeklyBackupsForWeeks,
        public int $keepMonthlyBackupsForMonths,
        public int $keepYearlyBackupsForYears,
        public int $deleteOldestBackupsWhenUsingMoreMegabytesThan,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            keepAllBackupsForDays: $data['keep_all_backups_for_days'],
            keepDailyBackupsForDays: $data['keep_daily_backups_for_days'],
            keepWeeklyBackupsForWeeks: $data['keep_weekly_backups_for_days'],
            keepMonthlyBackupsForMonths: $data['keep_monthly_backups_for_days'],
            keepYearlyBackupsForYears: $data['keep_yearly_backups_for_days'],
            deleteOldestBackupsWhenUsingMoreMegabytesThan: $data['delete_oldest_backups_when_using_more_megabytes_than'],
        );
    }
}
