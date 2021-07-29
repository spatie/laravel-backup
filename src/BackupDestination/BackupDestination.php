<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Spatie\Backup\Exceptions\InvalidBackupDestination;
use Spatie\Backup\Exceptions\InvalidBackupFile;

class BackupDestination
{
    protected ?Filesystem $disk;

    protected string $diskName;

    protected string $backupName;

    public ?Exception $connectionError = null;

    protected ?BackupCollection $backupCollectionCache = null;

    public function __construct(Filesystem $disk = null, string $backupName, string $diskName)
    {
        $this->disk = $disk;

        $this->diskName = $diskName;

        $this->backupName = (string)preg_replace('/[^a-zA-Z0-9.]/', '-', $backupName);
    }

    public function disk(): Filesystem
    {
        return $this->disk;
    }

    public function diskName(): string
    {
        return $this->diskName;
    }

    public function filesystemType(): string
    {
        if (is_null($this->disk)) {
            return 'unknown';
        }

        $adapterClass = $this->disk->getDriver()->getAdapter()::class;

        $filesystemType = last(explode('\\', $adapterClass));

        return strtolower($filesystemType);
    }

    public static function create(string $diskName, string $backupName): self
    {
        try {
            $disk = app(Factory::class)->disk($diskName);

            return new static($disk, $backupName, $diskName);
        } catch (Exception $exception) {
            $backupDestination = new static(null, $backupName, $diskName);

            $backupDestination->connectionError = $exception;

            return $backupDestination;
        }
    }

    public function write(string $file): void
    {
        if (! is_null($this->connectionError)) {
            throw InvalidBackupDestination::connectionError($this->diskName);
        }

        if (is_null($this->disk)) {
            throw InvalidBackupDestination::diskNotSet($this->backupName);
        }

        $destination = $this->backupName.'/'.pathinfo($file, PATHINFO_BASENAME);

        $handle = fopen($file, 'r+');

        $hasWritten = $this->disk->getDriver()->writeStream(
            $destination,
            $handle,
            $this->getDiskOptions()
        );

        if (is_resource($handle)) {
            fclose($handle);
        }

        if (! $hasWritten) {
            throw InvalidBackupFile::writeError($this->backupName());
        }
    }

    public function backupName(): string
    {
        return $this->backupName;
    }

    public function backups(): BackupCollection
    {
        if ($this->backupCollectionCache) {
            return $this->backupCollectionCache;
        }

        $files = [];

        if (! is_null($this->disk)) {
            // $this->disk->allFiles() may fail when $this->disk is not reachable
            // in that case we still want to send the notification
            try {
                $files = $this->disk->allFiles($this->backupName);
            } catch (Exception) {
            }
        }

        return $this->backupCollectionCache = BackupCollection::createFromFiles(
            $this->disk,
            $files
        );
    }

    public function connectionError(): Exception
    {
        return $this->connectionError;
    }

    public function getDiskOptions(): array
    {
        return config("filesystems.disks.{$this->diskName()}.backup_options") ?? [];
    }

    public function isReachable(): bool
    {
        if (is_null($this->disk)) {
            return false;
        }

        try {
            $this->disk->allFiles($this->backupName);

            return true;
        } catch (Exception $exception) {
            $this->connectionError = $exception;

            return false;
        }
    }

    public function usedStorage(): float
    {
        return $this->backups()->size();
    }

    public function newestBackup(): ?Backup
    {
        return $this->backups()->newest();
    }

    public function oldestBackup(): ?Backup
    {
        return $this->backups()->oldest();
    }

    public function newestBackupIsOlderThan(Carbon $date): bool
    {
        $newestBackup = $this->newestBackup();

        if (is_null($newestBackup)) {
            return true;
        }

        return $newestBackup->date()->gt($date);
    }

    public function fresh(): self
    {
        $this->backupCollectionCache = null;

        return $this;
    }
}
