<?php

use Spatie\Backup\Helpers\ConsoleOutput;

function consoleOutput(): ConsoleOutput
{
    return app(ConsoleOutput::class);
}

function is_generator($variable): bool
{
    if (! is_callable($variable)) {
    }
}
