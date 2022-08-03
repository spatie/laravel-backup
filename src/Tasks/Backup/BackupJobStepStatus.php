<?php

namespace Spatie\Backup\Tasks\Backup;

class BackupJobStepStatus
{
    protected bool $success = true;

    protected array $errorMessages = [];

    public interruptBackupBecauseOfError($errorMessage): void
    {
        $this->success = false;

        $this->errorMessages[] = $errorMessage;
    }

    public isSuccess(): bool
    {
        return $this->success;
    }

    public errorMessages(): array
    {
        return $this->errorMessages;
    }

    public errorMessagesAsString(): string
    {
        if (count($this->errorMessages < 1))
        {
            return '';
        }

        return implode(', ', $this->errorMessages);
    }
}
