<?php

namespace Spatie\Backup\Notifications\Notifications;

use Spatie\Backup\Exceptions\InvalidHealthCheck;
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
            ->subject(trans('backup::notifications.unhealthy_backup_found_subject', ['application_name' => $this->applicationName()]))
            ->line(trans('backup::notifications.unhealthy_backup_found_body', ['application_name' => $this->applicationName(), 'disk_name' => $this->diskName()]))
            ->line($this->problemDescription());

        $this->backupDestinationProperties()->each(function ($value, $name) use ($mailMessage) {
            $mailMessage->line("{$name}: $value");
        });

        if (optional($this->inspectionFailure())->wasUnexpected()) {
            $mailMessage
                ->line('Inspection: '.$this->inspectionFailure()->inspection()->name())
                ->line(trans('backup::notifications.exception_message', ['message' => $this->inspectionFailure()->reason()->getMessage()]))
                ->line(trans('backup::notifications.exception_trace', ['trace' => $this->inspectionFailure()->reason()->getTraceAsString()]));
        }

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        $slackMessage = (new SlackMessage)
            ->error()
            ->from(config('backup.notifications.slack.username'), config('backup.notifications.slack.icon'))
            ->to(config('backup.notifications.slack.channel'))
            ->content(trans('backup::notifications.unhealthy_backup_found_subject_title', ['application_name' => $this->applicationName(), 'problem' => $this->problemDescription()]))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields($this->backupDestinationProperties()->toArray());
            });

        if (optional($this->inspectionFailure())->wasUnexpected()) {
            $slackMessage
                ->attachment(function (SlackAttachment $attachment) {
                    $attachment
                        ->title('Inspection')
                        ->content($this->inspectionFailure()->inspection()->name());
                })
                ->attachment(function (SlackAttachment $attachment) {
                    $attachment
                        ->title(trans('backup::notifications.exception_message_title'))
                        ->content($this->inspectionFailure()->reason()->getMessage());
                })
                ->attachment(function (SlackAttachment $attachment) {
                    $attachment
                        ->title(trans('backup::notifications.exception_trace_title'))
                        ->content($this->inspectionFailure()->reason()->getTraceAsString());
                });
        }

        return $slackMessage;
    }

    protected function problemDescription(): string
    {
        $backupStatus = $this->event->backupDestinationStatus;

        if (! $backupStatus->isReachable()) {
            return trans('backup::notification.unhealthy_backup_found_not_reachable', ['error' => $backupStatus->connectionError()]);
        }

        if ($backupStatus->amountOfBackups() === 0) {
            return trans('backup::notifications.unhealthy_backup_found_empty');
        }

        if ($backupStatus->usesTooMuchStorage()) {
            return trans('backup::notifications.unhealthy_backup_found_full', ['disk_usage' => $backupStatus->humanReadableUsedStorage(), 'disk_limit' => $backupStatus->humanReadableAllowedStorage()]);
        }

        if ($backupStatus->newestBackupIsTooOld()) {
            return trans('backup::notifications.unhealthy_backup_found_old', ['date' => $backupStatus->dateOfNewestBackup()->format('Y/m/d h:i:s')]);
        }

        if ($this->inspectionFailure() && ! $this->inspectionFailure()->wasUnexpected()) {
            return $this->inspectionFailure()->reason()->getMessage();
        }

        return trans('backup::notifications.unhealthy_backup_found_unknown');
    }

    protected function inspectionFailure()
    {
        return $this->event->backupDestinationStatus->getFailedInspection();
    }

    public function setEvent(UnhealthyBackupWasFoundEvent $event)
    {
        $this->event = $event;

        return $this;
    }
}
