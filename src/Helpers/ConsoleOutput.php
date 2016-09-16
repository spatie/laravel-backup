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

    public function __call(string $method, array $arguments)
    {
        $consoleOutput = app(static::class);

        if (! $consoleOutput->output) {
            return;
        }

        $consoleOutput->output->$method($arguments[0]);
    }
}
