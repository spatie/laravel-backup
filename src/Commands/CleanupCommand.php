<?php

namespace Spatie\Backup\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Exceptions\InvalidCommand;

class CleanupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all backups older than specified number of days in config.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = config('laravel-backup');

        $this->guardAgainstInvalidConfiguration($config);

        //$date = Carbon::now()->subDays($config['cleanup']['maxAgeInDays']);

        collect(BackupDestinationFactory::createFromArray($config['backup']['destination']))
            ->each(function (BackupDestination $backupDestination) use ($date) {
                $backupDestination->deleteBackupsOlderThan($date);
            });
    }

    protected function guardAgainstInvalidConfiguration(array $config)
    {
        $maxAgeInDays = $config['cleanup']['maxAgeInDays'];

        if (!is_numeric($maxAgeInDays)) {
            throw InvalidCommand::create('maxAgeInDays should be numeric');
        }
        if ($maxAgeInDays <= 0) {
            throw InvalidCommand::create('maxAgeInDays should be higher than 0');
        }
    }
}
