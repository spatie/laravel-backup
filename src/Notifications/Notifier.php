<?php

namespace Spatie\Backup\Notifications;

use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Exception;

class Notifier
{
    /** @var array */
    protected $config;

    public function __construct()
    {
        $this->subject = config('laravel-backup.backup.name').' backups';
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

    public function backupHasFailed(Exception $exception, BackupDestination $backupDestination = null)
    {
        $extraMessage = $backupDestination ? "to {$backupDestination->getFilesystemType()}-filesystem" : '';

        $this->sendNotification(
            'whenBackupWasFailed',
            "{$this->subject} : error",
            "Failed to backup {$extraMessage} because: {$exception->getMessage()}",
            BaseSender::TYPE_ERROR
        );
    }

    public function cleanupWasSuccessFul(BackupDestination $backupDestination)
    {
        $this->sendNotification(
            'whenCleanupWasSuccessful',
            $this->subject,
            "Successfully cleaned up the backups on {$backupDestination->getFilesystemType()}-filesystem",
            BaseSender::TYPE_SUCCESS
        );
    }

    public function cleanupHasFailed(Exception $exception)
    {
        $this->sendNotification(
            'whenCleanupHasFailed',
            "{$this->subject} : error",
            "Failed to cleanup the backup because: {$exception->getMessage()}",
            BaseSender::TYPE_ERROR
        );
    }

    public function healthyBackupWasFound(BackupDestinationStatus $backupDestinationStatus)
    {
        $this->sendNotification(
            'whenHealthyBackupWasFound',
            "Healthy backup found for {$backupDestinationStatus->getBackupName()} on {$backupDestinationStatus->getFilesystemName()}-filesystem",
            "Backups on filesystem {$backupDestinationStatus->getFilesystemName()} are ok",
            BaseSender::TYPE_SUCCESS
        );
    }

    /**
     * @param \Spatie\Backup\Tasks\Monitor\BackupDestinationStatus $backupDestinationStatus
     */
    public function unHealthyBackupWasFound(BackupDestinationStatus $backupDestinationStatus)
    {
        $this->sendNotification(
            'whenUnHealthyBackupWasFound',
            "Unhealthy backup found for {$backupDestinationStatus->getBackupName()} on {$backupDestinationStatus->getFilesystemName()}-filesystem",
            UnhealthyBackupMessage::createForBackupDestinationStatus($backupDestinationStatus),
            BaseSender::TYPE_ERROR
        );
    }

    /**
     * @param string $eventName
     * @param string $subject
     * @param string $message
     * @param string $type
     */
    protected function sendNotification($eventName, $subject, $message, $type)
    {
        $senderNames = config("laravel-backup.notifications.events.{$eventName}");

        collect($senderNames)
            ->map(function ($senderName) {
                $className = $senderName;

                if (file_exists(__DIR__.'/Senders/'.ucfirst($senderName).'.php')) {
                    $className = '\\Spatie\\Backup\\Notifications\\Senders\\'.ucfirst($senderName);
                }

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
