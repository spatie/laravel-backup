<?php

namespace Spatie\Backup\Notifications\Notifications;

use Spatie\Backup\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Spatie\Backup\Events\UnhealthyBackupWasFound as UnhealthyBackupWasFoundEvent;

class UnhealthyBackupWasFound extends BaseNotification
{
    /** @var \Spatie\Backup\Events\UnhealthyBackupWasFound */
    protected $event;

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->error()
            ->subject(trans('laravel-backup::notifications.unhealthy_backup_found_subject', ['application_name' => $this->applicationName()]))
            ->line(trans('laravel-backup::notifications.unhealthy_backup_found_body', ['application_name' => $this->applicationName(), 'disk_name' => $this->diskName()]))
            ->line($this->problemDescription());

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->to(config('laravel-backup.notifications.slack.channel'))
            ->content(trans('laravel-backup::notifications.unhealthy_backup_found_subject_title', ['application_name' => $this->applicationName(), 'problem' => $this->problemDescription()]))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }

    protected function problemDescription(): string
    {
        $backupStatus = $this->event->backupDestinationStatus;

        if (! $backupStatus->isReachable()) {
            return trans('laravel-backup::notification.unhealthy_backup_found_not_reachable', ['error' => $backupStatus->connectionError()]);
        }

        if ($backupStatus->amountOfBackups() === 0) {
            return trans('laravel-backup::notifications.unhealthy_backup_found_empty');
        }

        if ($backupStatus->usesTooMuchStorage()) {
            return trans('laravel-backup::notifications.unhealthy_backup_found_full', ['disk_usage' => $backupStatus->humanReadableUsedStorage(), 'disk_limit' => $backupStatus->humanReadableAllowedStorage()]);
        }

        if ($backupStatus->newestBackupIsTooOld()) {
            return trans('laravel-backup::notifications.unhealthy_backup_found_old', ['date' => $backupStatus->dateOfNewestBackup()->format('Y/m/d h:i:s')]);
        }

        return trans('laravel-backup::notifications.unhealthy_backup_found_unknown');
    }

    public function setEvent(UnhealthyBackupWasFoundEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
