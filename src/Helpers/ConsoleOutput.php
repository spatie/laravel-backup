<?php

namespace Spatie\Backup\Helpers;

use Illuminate\Console\Command;

/**
 * @method info(string $message)
 * @method error(string $message)
 */
class ConsoleOutput
{
    protected ?Command $command = null;

    public function setCommand(Command $command): void
    {
        $this->command = $command;
    }

    public function __call(string $method, array $arguments)
    {
        $consoleOutput = app(static::class);

        if (! $consoleOutput->command) {
            return;
        }

        $consoleOutput->command->$method($arguments[0]);
    }
}
