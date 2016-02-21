<?php

namespace Spatie\Backup\Tasks\Backup;

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
    public static function create($path = '')
    {
        $fileSystem = new FileSystem();

        return (new static($fileSystem))->setPath($path);
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
    public function getPath($fileName)
    {
        if ($fileName === '') {
            return $this->path;
        }

        return "{$this->path}/{$fileName}";
    }

    /**
     * @param string $path
     *
     * @return \Spatie\Backup\Tasks\Backup\TemporaryDirectory
     */
    protected function setPath($path = '')
    {
        $tempPath = storage_path('laravel-backups/temp');

        if ($tempPath !== '') {
            $tempPath .= "/{$path}";
        }

        $this->path = $tempPath;

        $this->createTemporaryDirectory($tempPath);

        return $this;
    }

    /**
     * @param string $path
     */
    protected function createTemporaryDirectory($path)
    {
        $this->filesystem->makeDirectory($path, 0777, true, true);
    }

    public function delete()
    {
        $this->filesystem->deleteDirectory($this->path);
    }
}
