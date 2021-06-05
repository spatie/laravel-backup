<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class CannotWriteFile extends Exception
{
    public static function S3MultipartUploadException(string $reason): self
    {
        return new static($reason);
    }
}
