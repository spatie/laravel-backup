<?php

namespace Spatie\Backup\BackupDestination;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use InvalidArgumentException;
use Spatie\Backup\Exceptions\InvalidBackupFile;
use Spatie\Backup\Tasks\Backup\BackupJob;

class Backup
{
    protected bool $exists = true;

    protected ?Carbon $date = null;

    protected ?int $size = null;

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
        if ($this->date === null) {
            try {
                $basename = basename($this->path);

                $this->date = Carbon::createFromFormat(BackupJob::FILENAME_FORMAT, $basename);
            } catch (InvalidArgumentException) {
                $this->date = Carbon::createFromTimestamp($this->disk->lastModified($this->path));
            }
        }

        return $this->date;
    }

    public function sizeInBytes(): float
    {
        if ($this->size === null) {
            if (! $this->exists()) {
                return 0;
            }

            $this->size = $this->disk->size($this->path);
        }

        return $this->size;
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
            consoleOutput()->error("Failed to delete backup `{$this->path}`.");

            return;
        }

        $this->exists = false;

        consoleOutput()->info("Deleted backup `{$this->path}`.");
    }
}
