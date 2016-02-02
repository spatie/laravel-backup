<?php

namespace Spatie\Backup\Notifications\Handlers;

use Mail;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Notifications\BaseNotificationHandler;

class MailsErrors extends BaseNotificationHandler
{
    public function whenBackupHasFailed(BackupHasFailed $event)
    {
        $message = 'backup has failed because '.$event->error->getMessage();

        Mail::raw($message, function ($message) {

            $emailConfig = config('laravel-backup.notifications.email');

            $message
                ->from($emailConfig['from'])
                ->to($emailConfig['to']);
        });
    }
}
