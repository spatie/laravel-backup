<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;

class MonitorCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'backup:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor the health of all backups.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    }
}
