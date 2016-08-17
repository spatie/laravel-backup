<?php

namespace Spatie\Backup\Helpers;

class ConsoleOutput
{
    /** @var \Illuminate\Console\OutputStyle */
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
    public function __call($method, array $arguments)
    {
        $consoleOutput = app(static::class);

        if (! $consoleOutput->output) {
            return;
        }

        $consoleOutput->output->$method($arguments[0]);
    }
}
