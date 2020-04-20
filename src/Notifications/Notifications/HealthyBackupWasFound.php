<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\HealthyBackupWasFound as HealthyBackupWasFoundEvent;
use Spatie\Backup\Notifications\BaseNotification;

class HealthyBackupWasFound extends BaseNotification
{
    /** @var \Spatie\Backup\Events\HealthyBackupWasFound */
    protected $event;

    public function __construct(HealthyBackupWasFoundEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->from(config('backup.notifications.mail.from.address', config('mail.from.address')), config('backup.notifications.mail.from.name', config('mail.from.name')))
            ->subject(trans('backup::notifications.healthy_backup_found_subject', ['application_name' => $this->applicationName(), 'disk_name' => $this->diskName()]))
            ->line(trans('backup::notifications.healthy_backup_found_body', ['application_name' => $this->applicationName()]));

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->from(config('backup.notifications.slack.username'), config('backup.notifications.slack.icon'))
            ->to(config('backup.notifications.slack.channel'))
            ->content(trans('backup::notifications.healthy_backup_found_subject_title', ['application_name' => $this->applicationName()]))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }
}
