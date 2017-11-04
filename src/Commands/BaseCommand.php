<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\Backup\Exceptions\BackupsDisabled;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends Command
{
    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        if (! config('backup.backup.run', true)) {
            throw BackupsDisabled::disabled();
        }

        app(ConsoleOutput::class)->setOutput($this);

        return parent::run($input, $output);
    }
}
