<?php

namespace Spatie\Backup\Helpers;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;

class File
{
    /** @var array */
    protected static $allowedMimeTypes = [
        'application/zip',
        'application/x-zip',
        'application/x-gzip',
    ];

    public function isZipFile(?Filesystem $disk, string $path): bool
    {
        if ($this->hasZipExtension($path)) {
            return true;
        }

        return $this->hasAllowedMimeType($disk, $path);
    }

    protected function hasZipExtension(string $path): bool
    {
        return pathinfo($path, PATHINFO_EXTENSION) === 'zip';
    }

    protected function hasAllowedMimeType(?Filesystem $disk, string $path)
    {
        return in_array($this->mimeType($disk, $path), self::$allowedMimeTypes);
    }

    protected function mimeType(?Filesystem $disk, string $path)
    {
        try {
            if ($disk && method_exists($disk, 'mimeType')) {
                return $disk->mimeType($path) ?: false;
            }
        } catch (Exception $exception) {
            // Some drivers throw exceptions when checking mime types, we'll
            // just fallback to `false`.
        }

        return false;
    }
}
