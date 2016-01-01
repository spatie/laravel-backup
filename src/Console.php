<?php

namespace Spatie\Backup;

use Symfony\Component\Process\Process;

class Console
{
    /**
     * Run a command in the shell.
     *
     * @param $command
     * @param int $timeoutInSeconds
     * @param array $env
     *
     * @return bool|string
     */
    public function run($command, $timeoutInSeconds = 60, array $env = null)
    {
        $process = new Process($command);

        $process->setTimeout($timeoutInSeconds);

        if ($env != null) {
            $process->setEnv($env);
        }

        $process->run();

        if ($process->isSuccessful()) {
            return true;
        } else {
            return $process->getErrorOutput();
        }
    }
}
