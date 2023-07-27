<?php

use Spatie\Backup\Helpers\ConsoleOutput;

function consoleOutput(): ConsoleOutput
{
    return app(ConsoleOutput::class);
}

function isSleepHelperAvailable()
{
    return class_exists('Illuminate\Support\Sleep');
}
