<?php

namespace Spatie\Backup\Tests\TestSupport\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Notifications\BaseNotification;

class BackupHasFailedNotification extends BaseNotification
{
    public function __construct(
        public BackupHasFailed $event,
    ) {}

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('Custom: Backup failed');
    }
}
