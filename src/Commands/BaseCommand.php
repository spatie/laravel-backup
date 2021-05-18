<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\SignalAwareCommand\SignalAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends SignalAwareCommand
{
    protected array $handlesSignals = [];

    public function __construct()
    {
        if (PHP_OS_FAMILY !== 'Windows' && defined('SIGINT')) {
            $this->handlesSignals[] = SIGINT;
        }

        parent::__construct();
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        app(ConsoleOutput::class)->setCommand($this);

        return parent::run($input, $output);
    }
}
