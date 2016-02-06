<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use Spatie\Backup\Tasks\Monitor\BackupStatus;

class OverviewCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:overview';

    /**
     * The console command description.
     *k
     * @var string
     */
    protected $description = 'Display an overview of all backups.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $config = config('monitorBackups');

        foreach($config as $monitorProperties) {
            $backupStatus = new BackupStatus($monitorProperties);

            echo $backupStatus->getName();
        }
    }
}
