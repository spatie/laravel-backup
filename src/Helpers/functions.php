<?php

use Spatie\Backup\Helpers\ConsoleOutput;

/**
 * @return \Spatie\Backup\Helpers\ConsoleOutput
 */
function consoleOutput()
{
    return app(ConsoleOutput::class);
}
