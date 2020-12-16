<?php

namespace Spatie\Backup\Helpers;

use Illuminate\Console\Command;

class ConsoleOutput
{
    protected ?Command $command = null;

    public function setCommand(Command $command)
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
