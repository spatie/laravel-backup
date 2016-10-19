<?php

namespace Spatie\Backup\Tasks\Backup;

use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;

class TemporaryDirectory
{
    /** @var \Illuminate\Filesystem\Filesystem */
    protected $filesystem;

    /** @var string */
    protected $path;

    /**
     * @param string $path
     *
     * @return mixed
     */
    public static function create(string $path = ''): TemporaryDirectory
    {
        $filesystem = new Filesystem();

        return (new static($filesystem))->setPath($path.'/'.Carbon::now()->format('Y-m-d-H-i-s'));
    }

    /**
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     */
    private function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    public function path(string $fileName = ''): string
    {
        if ($fileName === '') {
            return $this->path;
        }

        $fullPath = "{$this->path}/{$fileName}";

        if ($this->isProbablyADirectoryName($fileName)) {
            $this->createTemporaryDirectory($fullPath);
        }

        return $fullPath;
    }

    protected function isProbablyADirectoryName(string $fileName): bool
    {
        return ! str_contains($fileName, '.');
    }

    protected function setPath(string $path = ''): TemporaryDirectory
    {
        $tempPath = storage_path('app/laravel-backup/temp');

        if ($tempPath !== '') {
            $tempPath .= "{$path}";
        }

        $tempPath = rtrim($tempPath, '/');

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
        if (! $this->filesystem->exists($this->path)) {
            return;
        }

        $this->filesystem->deleteDirectory($this->path);
    }
}
