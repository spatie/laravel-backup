<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class BackupVerificationFailed extends Exception
{
    public static function couldNotOpenZip(string $pathToZip, int $errorCode): self
    {
        return new static("Backup verification failed: could not open the zip file at `{$pathToZip}`. ZipArchive error code: {$errorCode}.");
    }

    public static function zipIsEmpty(string $pathToZip): self
    {
        return new static("Backup verification failed: the zip file at `{$pathToZip}` is empty.");
    }

    public static function unexpectedFileCount(int $expectedFileCount, int $actualFileCount): self
    {
        return new static("Backup verification failed: expected {$expectedFileCount} files in the archive, but found {$actualFileCount}.");
    }
}
