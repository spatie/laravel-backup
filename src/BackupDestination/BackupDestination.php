<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use ReflectionMethod;
use Spatie\Backup\Exceptions\InvalidBackupDestination;

class BackupDestination
{
    public ?Exception $connectionError = null;

    protected ?BackupCollection $backupCollectionCache = null;

    public function __construct(protected ?Filesystem $disk, protected string $backupName, protected string $diskName) {}

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
        if ($this->disk === null) {
            return 'unknown';
        }

        $adapterClass = $this->disk->getAdapter()::class;

        $filesystemType = last(explode('\\', $adapterClass));

        return strtolower((string) $filesystemType);
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
        if ($this->connectionError !== null) {
            throw InvalidBackupDestination::connectionError($this->diskName);
        }

        if ($this->disk === null) {
            throw InvalidBackupDestination::diskNotSet($this->backupName);
        }

        $destination = $this->backupName.'/'.pathinfo($file, PATHINFO_BASENAME);

        $handle = fopen($file, 'r+');

        try {
            $this->disk->getDriver()->writeStream(
                $destination,
                $handle,
                $this->getDiskOptions(),
            );
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public function backupName(): string
    {
        return $this->backupName;
    }

    public function setBackupName(string $backupName): self
    {
        $this->backupName = $backupName;

        return $this;
    }

    public function backups(): BackupCollection
    {
        if ($this->backupCollectionCache) {
            return $this->backupCollectionCache;
        }

        if ($this->canListFileAttributes()) {
            try {
                return $this->backupCollectionCache = BackupCollection::createFromListing(
                    $this->disk,
                    $this->disk->getDriver()->listContents($this->backupName, true)->sortByPath()->toArray(),
                );
            } catch (Exception) {
                return $this->backupCollectionCache = BackupCollection::createFromFiles($this->disk, []);
            }
        }

        $files = [];

        if ($this->disk !== null) {
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

    protected function canListFileAttributes(): bool
    {
        // Listings (e.g. S3 ListObjectsV2) already include file sizes, saving a HEAD request per backup.
        // Disks that customise allFiles() keep using it so their behaviour doesn't change.
        if (! $this->disk instanceof FilesystemAdapter) {
            return false;
        }

        foreach (['allFiles', 'files'] as $method) {
            if ((new ReflectionMethod($this->disk, $method))->getDeclaringClass()->getName() !== FilesystemAdapter::class) {
                return false;
            }
        }

        return true;
    }

    public function connectionError(): ?Exception
    {
        return $this->connectionError;
    }

    /** @return array<string, string> */
    public function getDiskOptions(): array
    {
        return config("filesystems.disks.{$this->diskName()}.backup_options") ?? [];
    }

    public function isReachable(): bool
    {
        if ($this->disk === null) {
            return false;
        }

        try {
            $this->disk->files($this->backupName);

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

    /** @deprecated Will be removed in the next major version. */
    public function newestBackupIsOlderThan(Carbon $date): bool
    {
        $newestBackup = $this->newestBackup();

        if ($newestBackup === null) {
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
