<?php

namespace Spatie\Backup\Commands;

use Spatie\Backup\Config\Config;
use Spatie\Backup\Exceptions\InvalidCommand;
use Spatie\Backup\Support\BackupLogger;
use Spatie\Backup\Tasks\Cleanup\CleanupStrategy;
use Spatie\SignalAwareCommand\SignalAwareCommand;
use Spatie\TemporaryDirectory\TemporaryDirectory;
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

    /**
     * Make the config file passed via `--config` the active backup config for
     * the rest of this process. Everything that resolves the config from the
     * container (`app(Config::class)`) or reads `config('backup.*')` directly
     * needs to see the alternate config as well, not just the command itself.
     */
    protected function resolveConfig(): Config
    {
        $configKey = $this->option('config');

        if (! $configKey) {
            return app(Config::class);
        }

        $configArray = config($configKey);

        if (! is_array($configArray)) {
            throw InvalidCommand::create("There is no config file named `{$configKey}`.");
        }

        config()->set('backup', $configArray);

        $config = Config::fromArray($configArray);

        app()->instance(Config::class, $config);
        app()->bind(CleanupStrategy::class, $config->cleanup->strategy);
        app()->bind('backup-temporary-project', fn () => new TemporaryDirectory(
            $config->backup->temporaryDirectory ?? storage_path('app/backup-temp')
        ));

        return $config;
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
