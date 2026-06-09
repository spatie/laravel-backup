<?php

namespace Spatie\Backup\Listeners;

use Spatie\Backup\Config\Config;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Exceptions\BackupFailed;
use Spatie\Backup\Tasks\Backup\Zip;
use ZipArchive;

/**
 * @deprecated Encryption is now applied by {@see Zip} while the archive
 *             is built. This listener is no longer registered.
 */
class EncryptBackupArchive
{
    public function __construct(protected Config $config) {}

    public function handle(BackupZipWasCreated $event): void
    {
        if (! $this->shouldEncrypt()) {
            return;
        }

        $zip = new ZipArchive;

        $result = $zip->open($event->pathToZip);

        if ($result !== true) {
            throw BackupFailed::from(new \Exception("Failed to open zip file for encryption at '{$event->pathToZip}'. ZipArchive error code: {$result}"));
        }

        $this->encrypt($zip);

        $zip->close();
    }

    protected function encrypt(ZipArchive $zip): void
    {
        $zip->setPassword($this->config->backup->password);

        $algorithm = $this->config->backup->encryption->algorithm();

        foreach (range(0, $zip->numFiles - 1) as $i) {
            $zip->setEncryptionIndex($i, $algorithm);
        }
    }

    public function shouldEncrypt(): bool
    {
        $password = $this->config->backup->password;
        $encryption = $this->config->backup->encryption;

        if ($password === null) {
            return false;
        }

        return $encryption->shouldEncrypt();
    }
}
