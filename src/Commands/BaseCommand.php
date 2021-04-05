<?php

namespace Spatie\Backup\Commands;

use Illuminate\Console\Command;
use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\SignalAwareCommand\SignalAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseCommand extends SignalAwareCommand
{
    protected array $handlesSignals = [SIGINT];

    public function run(InputInterface $input, OutputInterface $output): int
    {
        app(ConsoleOutput::class)->setCommand($this);

        return parent::run($input, $output);
    }
}
