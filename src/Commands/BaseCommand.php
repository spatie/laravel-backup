<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\Support\BackupLogger;
use Spatie\SignalAwareCommand\SignalAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SignalRegistry\SignalRegistry;

abstract class BaseCommand extends SignalAwareCommand
{
    /** @var array<int> */
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
        app(BackupLogger::class)->onMessage(function (string $level, string $message) {
            match ($level) {
                'error' => $this->error($message),
                'warning' => $this->warn($message),
                default => $this->info($message),
            };
        });

        return parent::run($input, $output);
    }

    protected function runningInConsole(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg']);
    }

    /** @return array<int> */
    public function getSubscribedSignals(): array
    {
        return $this->handlesSignals;
    }
}
