<?php

namespace Spatie\Backup\Listeners;

use Spatie\Backup\Events\BackupZipWasCreated;
use ZipArchive;

class EncryptBackupArchive
{
    public function handle(BackupZipWasCreated $event): void
    {
        if (! self::shouldEncrypt()) {
            return;
        }

        $zip = new ZipArchive();

        $zip->open($event->pathToZip);

        $this->encrypt($zip);

        $zip->close();
    }

    protected function encrypt(ZipArchive $zip): void
    {
        $zip->setPassword(static::getPassword());

        foreach (range(0, $zip->numFiles - 1) as $i) {
            $zip->setEncryptionIndex($i, static::getAlgorithm());
        }
    }

    public static function shouldEncrypt(): bool
    {
        $password = static::getPassword();
        $algorithm = static::getAlgorithm();

        if ($password === null) {
            return false;
        }

        if ($algorithm === null) {
            return false;
        }

        if ($algorithm === false) {
            return false;
        }

        return true;
    }

    protected static function getPassword(): ?string
    {
        return config('backup.backup.password');
    }

    protected static function getAlgorithm(): ?int
    {
        $encryption = config('backup.backup.encryption');

        if ($encryption === 'default') {
            $encryption = defined("\ZipArchive::EM_AES_256")
                ? ZipArchive::EM_AES_256
                : null;
        }

        return $encryption;
    }
}
