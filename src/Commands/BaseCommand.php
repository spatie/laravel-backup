<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\Helpers\ConsoleOutput;
use Spatie\SignalAwareCommand\SignalAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SignalRegistry\SignalRegistry;

abstract class BaseCommand extends SignalAwareCommand
{
    protected array $handlesSignals = [];

    public function __construct()
    {
        if ($this->runningInConsole() && SignalRegistry::isSupported()) {
            $this->handlesSignals[] = SIGINT;
        }

        parent::__construct();
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        app(ConsoleOutput::class)->setCommand($this);

        return parent::run($input, $output);
    }

    protected function runningInConsole(): bool
    {
        return in_array(php_sapi_name(), ['cli', 'phpdbg']);
    }

    public function getSubscribedSignals(): array
    {
        return $this->handlesSignals;
    }
}
