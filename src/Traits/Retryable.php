<?php

namespace Spatie\Backup\Traits;

use Illuminate\Support\Sleep;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait Retryable
{
    protected int $tries = 1;

    protected int $currentTry = 1;

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->currentTry = 1;

        return parent::run($input, $output);
    }

    protected function shouldRetry(): bool
    {
        if ($this->tries <= 1) {
            return false;
        }

        return $this->currentTry < $this->tries;
    }

    protected function hasRetryDelay(string $type): bool
    {
        return ! empty($this->getRetryDelay($type));
    }

    protected function sleepFor(int $seconds = 0): void
    {
        Sleep::for($seconds)->seconds();
    }

    protected function setTries(string $type): void
    {
        if ($this->option('tries')) {
            $this->tries = (int) $this->option('tries');

            return;
        }

        $this->tries = match ($type) {
            'backup' => $this->config->backup->tries,
            'cleanup' => $this->config->cleanup->tries,
            default => 1,
        };
    }

    protected function getRetryDelay(string $type): int
    {
        return match ($type) {
            'backup' => $this->config->backup->retryDelay,
            'cleanup' => $this->config->cleanup->retryDelay,
            default => 0,
        };
    }
}
