<?php

namespace Spatie\Backup\Helpers;

use Illuminate\Console\OutputStyle;

class ConsoleOutput
{
    /** @var OutputStyle  */
    protected $output;

    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function __call($method, $arguments)
    {
        $consoleOutput = app(static::class);

        if (!$consoleOutput->output) {
            return;
        }

        $consoleOutput->output->$method($arguments[0]);
    }
}
