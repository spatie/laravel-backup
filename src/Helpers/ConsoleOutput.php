<?php

namespace Spatie\Backup\Helpers;

use Illuminate\Console\OutputStyle;

class ConsoleOutput
{
    /** @var OutputStyle  */
    protected $output;

    /**
     * @param $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * @param string $method
     * @param array  $arguments
     */
    public function __call($method, $arguments)
    {
        $consoleOutput = app(static::class);

        if (!$consoleOutput->output) {
            return;
        }

        $consoleOutput->output->$method($arguments[0]);
    }
}
