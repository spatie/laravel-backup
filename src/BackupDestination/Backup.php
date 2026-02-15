<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use InvalidArgumentException;
use Spatie\Backup\Exceptions\InvalidBackupFile;
use Spatie\Backup\Tasks\Backup\BackupJob;

class Backup
{
    public private(set) bool $exists = true;

    private ?Carbon $cachedDate = null;

    private ?int $cachedSize = null;

    public function __construct(
        protected Filesystem $disk,
        protected string $path,
    ) {
        $this->exists = $this->disk->exists($this->path);
    }

    public function disk(): Filesystem
    {
        return $this->disk;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function date(): Carbon
    {
        if ($this->cachedDate === null) {
            try {
                $basename = basename($this->path);

                $this->cachedDate = Carbon::createFromFormat(BackupJob::FILENAME_FORMAT, $basename);
            } catch (InvalidArgumentException) {
                $this->cachedDate = Carbon::createFromTimestamp($this->disk->lastModified($this->path));
            }
        }

        return $this->cachedDate;
    }

    public function sizeInBytes(): float
    {
        if ($this->cachedSize === null) {
            if (! $this->exists()) {
                return 0;
            }

            $this->cachedSize = $this->disk->size($this->path);
        }

        return $this->cachedSize;
    }

    /** @return resource */
    public function stream()
    {
        return throw_unless(
            $this->disk->readStream($this->path),
            InvalidBackupFile::readError($this)
        );
    }

    public function delete(): void
    {
        if (! $this->disk->delete($this->path)) {
            backupLogger()->error("Failed to delete backup `{$this->path}`.");

            return;
        }

        $this->exists = false;

        backupLogger()->info("Deleted backup `{$this->path}`.");
    }
}
