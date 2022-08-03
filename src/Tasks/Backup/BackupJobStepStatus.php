<?php

namespace Spatie\Backup\Tasks\Backup;

class BackupJobStepStatus
{
    protected bool $success = true;

    protected array $errorMessages = [];

    public function interruptBackupBecauseOfError($errorMessage): void
    {
        $this->success = false;

        $this->errorMessages[] = $errorMessage;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function errorMessages(): array
    {
        return $this->errorMessages;
    }

    public function errorMessagesAsString(): string
    {
        if (count($this->errorMessages) < 1)
        {
            return '';
        }

        return implode(', ', $this->errorMessages);
    }
}
