<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\HealthyBackupWasFound;
use Spatie\Backup\Notifications\BaseNotification;
use Spatie\Backup\Notifications\Channels\Discord\DiscordMessage;

class HealthyBackupWasFoundNotification extends BaseNotification
{
    public function __construct(
        public HealthyBackupWasFound $event,
    ) {}

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage())
            ->from($this->config()->notifications->mail->from->address, $this->config()->notifications->mail->from->name)
            ->subject(trans('backup::notifications.healthy_backup_found_subject', ['application_name' => $this->applicationName(), 'disk_name' => $this->diskName()]))
            ->line(trans('backup::notifications.healthy_backup_found_body', ['application_name' => $this->applicationName()]));

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: {$value}");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage())
            ->success()
            ->from($this->config()->notifications->slack->username, $this->config()->notifications->slack->icon)
            ->to($this->config()->notifications->slack->channel)
            ->content(trans('backup::notifications.healthy_backup_found_subject_title', ['application_name' => $this->applicationName()]))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }

    public function toDiscord(): DiscordMessage
    {
        return (new DiscordMessage())
            ->success()
            ->from($this->config()->notifications->discord->username, $this->config()->notifications->discord->avatar_url)
            ->title(
                trans('backup::notifications.healthy_backup_found_subject_title', [
                    'application_name' => $this->applicationName(),
                ])
            )->fields($this->backupDestinationProperties()->toArray());
    }
}
