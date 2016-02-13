<?php

namespace Spatie\Backup\Notifications;

use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\BackupStatus;
use Throwable;

class Notifier
{
    /** @var array */
    protected $config;

    public function __construct()
    {
        $this->subject = config('laravel-backup.name').' backups';
    }

    public function backupWasSuccessful()
    {
        $this->sendNotification(
            'whenBackupWasSuccessful',
            $this->subject,
            'Successfully took a new backup',
            BaseSender::TYPE_SUCCESS
        );
    }

    public function backupHasFailed(Throwable $error)
    {
        $this->sendNotification(
            'whenBackupWasFailed',
            "{$this->subject} : error",
            "Failed to backup because {$error->getMessage()}",
            BaseSender::TYPE_ERROR
        );
    }

    public function cleanupWasSuccessFul(BackupDestination $backupDestination)
    {
        $this->sendNotification(
            'whenCleanupWasSuccessful',
            $this->subject,
            "Successfully cleanup the backups on {$backupDestination->getFilesystemType()}",
            BaseSender::TYPE_SUCCESS
        );
    }

    public function cleanupHasFailed(Throwable $error)
    {
        $this->sendNotification(
            'whencleanupHasFailed',
            "{$this->subject} : error",
            "Successfully cleanup the backups on {$backupDestination->getFilesystemType()}",
            BaseSender::TYPE_ERROR
        );
    }

    public function HealthyBackupWasFound(BackupStatus $backupStatus)
    {
        $this->sendNotification(
            'whenHealthyBackupWasFound',
            $this->subject,
            "Healthy backup found {$backupStatus->getName()}",
            BaseSender::TYPE_SUCCESS
        );
    }

    public function unHealthyBackupWasFound(BackupStatus $backupStatus)
    {
        $this->sendNotification(
            'whenUnHealthyBackupWasFound',
            "{$this->subject} : error",
            "Unhealthy backup found {$backupStatus->getName()}",
            BaseSender::TYPE_ERROR
        );
    }

    protected function sendNotification(string $eventName, string $subject, string $message, string $type)
    {
        $senderNames = config("laravel-backup.notifications.events.{$eventName}");

        //dd($senderNames, config("laravel-backup.notifications.events"), $eventName);

        collect($senderNames)
            ->map(function (string $senderName) {
                $className = '\\Spatie\\Backup\\Notifications\\Senders\\'.ucfirst($senderName);

                return app($className);
            })
            ->each(function (SendsNotifications $sender) use ($subject, $message, $type) {
                $sender
                    ->setSubject($subject)
                    ->setMessage($message)
                    ->setType($type)
                    ->send();
            });
    }
}
