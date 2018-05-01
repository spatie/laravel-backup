<?php

namespace Spatie\Backup\BackupDestination;

use Exception;

class BackupPath
{
    /** @var array */
    protected static $allowedMimeTypes = [
        'application/zip',
        'application/x-zip',
        'application/x-gzip',
    ];

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem|null $disk
     * @param string $path
     *
     * @return bool
     */
    public function isBackupFile($disk, string $path) : bool
    {
        return $this->hasZipExtension($path) ?: $this->hasAllowedMimeType($disk, $path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    protected function hasZipExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION) === 'zip';
    }

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem|null $disk
     * @param string $path
     *
     * @return bool
     */
    protected function hasAllowedMimeType($disk, $path)
    {
        return in_array($this->mimeType($disk, $path), self::$allowedMimeTypes);
    }

    /**
     * @param \Illuminate\Contracts\Filesystem\Filesystem|null $disk
     * @param string $path
     *
     * @return string|false
     */
    protected function mimeType($disk, $path)
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
