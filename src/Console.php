<?php namespace Spatie\Backup;

use Symfony\Component\Process\Process;

class Console
{
    /**
     * Run a command in the shell.
     *
     * @param $command
     * @param $timeoutInSeconds
     * @return bool|string
     */
    public function run($command, $timeoutInSeconds = 60)
    {
        $process = new Process($command);

        $process->setTimeout($timeoutInSeconds);

        $process->run();

        if ($process->isSuccessful()) {
            return true;
        } else {
            return $process->getErrorOutput();
        }
    }
}
