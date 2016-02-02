<?php

namespace Spatie\Backup\Notifications\Handlers;

use Mail;
use Log;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Notifications\BaseNotificationHandler;

class MailsErrors extends BaseNotificationHandler
{

    public function whenBackupHasFailed(BackupHasFailed $event)
    {
        $message = 'backup has failed because ' . $event->error->getMessage();

        Mail::send($message, function ($message) {
            $message->to(config('laravel-backup.notifications.handlers.email.to'));
        });
    }
}
