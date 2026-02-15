<?php

namespace Spatie\Backup\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Notifications\BaseNotification;
use Spatie\Backup\Notifications\Channels\Discord\DiscordMessage;

class UnhealthyBackupWasFoundNotification extends BaseNotification
{
    public function __construct(
        public UnhealthyBackupWasFound $event,
    ) {}

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->error()
            ->from($this->config()->notifications->mail->from->address, $this->config()->notifications->mail->from->name)
            ->subject(trans('backup::notifications.unhealthy_backup_found_subject', ['application_name' => $this->applicationName()]))
            ->line(trans('backup::notifications.unhealthy_backup_found_body', ['application_name' => $this->applicationName(), 'disk_name' => $this->event->diskName]));

        foreach ($this->event->failureMessages as $failure) {
            $mailMessage->line("- [{$failure['check']}] {$failure['message']}");
        }

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: {$value}");
        });

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        $problemDescription = $this->event->failureMessages
            ->map(fn (array $f) => "[{$f['check']}] {$f['message']}")
            ->implode("\n");

        return (new SlackMessage)
            ->error()
            ->from($this->config()->notifications->slack->username, $this->config()->notifications->slack->icon)
            ->to($this->config()->notifications->slack->channel)
            ->content(trans('backup::notifications.unhealthy_backup_found_subject_title', ['application_name' => $this->applicationName(), 'problem' => $problemDescription]))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });
    }

    public function toDiscord(): DiscordMessage
    {
        $problemDescription = $this->event->failureMessages
            ->map(fn (array $f) => "[{$f['check']}] {$f['message']}")
            ->implode("\n");

        return (new DiscordMessage)
            ->error()
            ->from($this->config()->notifications->discord->username, $this->config()->notifications->discord->avatar_url)
            ->title(
                trans('backup::notifications.unhealthy_backup_found_subject_title', [
                    'application_name' => $this->applicationName(),
                    'problem' => $problemDescription,
                ])
            )->fields($this->backupDestinationProperties()->toArray());
    }

    /** @return array<string, mixed> */
    public function toWebhook(): array
    {
        return [
            'type' => 'unhealthy_backup_found',
            'application_name' => $this->applicationName(),
            'disk_name' => $this->event->diskName,
            'backup_name' => $this->event->backupName,
            'failures' => $this->event->failureMessages->toArray(),
        ];
    }
}
