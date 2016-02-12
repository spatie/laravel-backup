<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Filesystem\Filesystem;

class TemporaryDirectory
{
    /** @var \Illuminate\Filesystem\Filesystem */
    protected $filesystem;

    /** @var string */
    protected $path;

    public static function create(string $path = '')
    {
        $fileSystem = new FileSystem();

        return (new static($fileSystem))->setPath($path);
    }

    private function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function getPath(string $fileName) : string
    {
        if ($fileName === '') {
            return $this->path;
        }

        return "{$this->path}/{$fileName}";
    }

    protected function setPath(string $path = '') : TemporaryDirectory
    {
        $tempPath = storage_path('laravel-backups/temp');

        if ($tempPath !== '') {
            $tempPath .= "/{$path}";
        }

        $this->path = $tempPath;

        $this->createTemporaryDirectory($tempPath);

        return $this;
    }

    protected function createTemporaryDirectory(string $path)
    {
        $this->filesystem->makeDirectory($path, 0777, true, true);
    }

    public function delete()
    {
        $this->filesystem->deleteDirectory($this->path);
    }
}
