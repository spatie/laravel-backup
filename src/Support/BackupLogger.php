<?php

namespace Spatie\Backup\Support;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class BackupLogger
{
    protected ?LoggerInterface $logger = null;

    /** @var array<callable> */
    protected array $listeners = [];

    public function useLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function onMessage(callable $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function clearListeners(): void
    {
        $this->listeners = [];
    }

    public function info(string $message): void
    {
        $this->log('info', $message);
    }

    public function error(string $message): void
    {
        $this->log('error', $message);
    }

    public function comment(string $message): void
    {
        $this->log('info', $message);
    }

    public function warn(string $message): void
    {
        $this->log('warning', $message);
    }

    protected function log(string $level, string $message): void
    {
        foreach ($this->listeners as $listener) {
            $listener($level, $message);
        }

        $logger = $this->logger ?? Log::getFacadeRoot();

        $logger?->log($level, "[backup] {$message}");
    }
}
