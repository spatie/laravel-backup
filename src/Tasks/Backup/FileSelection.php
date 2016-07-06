<?php

namespace Spatie\Backup\Tasks\Backup;

use Symfony\Component\Finder\Finder;

class FileSelection
{
    /** @var \Illuminate\Support\Collection */
    protected $includeFilesAndDirectories;

    /** @var \Illuminate\Support\Collection */
    protected $excludeFilesAndDirectories;

    /** @var bool */
    protected $shouldFollowLinks = false;

    /**
     * @param array|string $includeFilesAndDirectories
     *
     * @return \Spatie\Backup\Tasks\Backup\FileSelection
     */
    public static function create($includeFilesAndDirectories = [])
    {
        return new static($includeFilesAndDirectories);
    }

    /**
     * @param array|string $includeFilesAndDirectories
     */
    public function __construct($includeFilesAndDirectories)
    {
        $this->includeFilesAndDirectories = $this->sanitize($includeFilesAndDirectories);

        $this->excludeFilesAndDirectories = collect();
    }

    /**
     * Do not included the given files and directories.
     *
     * @param array|string $excludeFilesAndDirectories
     *
     * @return \Spatie\Backup\Tasks\Backup\FileSelection
     */
    public function excludeFilesFrom($excludeFilesAndDirectories)
    {
        $this->excludeFilesAndDirectories = $this->sanitize($excludeFilesAndDirectories);

        return $this;
    }

    /**
     * Enable or disable the following of symlinks.
     *
     * @param bool $shouldFollowLinks
     *
     * @return \Spatie\Backup\Tasks\Backup\FileSelection
     */
    public function shouldFollowLinks($shouldFollowLinks)
    {
        $this->shouldFollowLinks = $shouldFollowLinks;

        return $this;
    }

    /**
     * @return Generator|string
     */
    public function getSelectedFiles()
    {
        if ($this->includeFilesAndDirectories->isEmpty()) {
            return;
        }

        $finder = (new Finder())
            ->ignoreDotFiles(false)
            ->ignoreVCS(false)
            ->files();

        if ($this->shouldFollowLinks) {
            $finder->followLinks();
        }

        $finder->in($this->includedDirectories());

        foreach ($this->includedFiles() as $includedFile) {
            yield $includedFile;
        }

        foreach ($finder->getIterator() as $file) {

            if ($this->shouldExclude($file)) {
                continue;
            }

            yield $file->getPathname();
        }
    }

    /**
     * @return array
     */
    protected function includedFiles()
    {
        return $this->includeFilesAndDirectories->filter(function ($path) {
            return is_file($path);
        })->toArray();
    }

    /**
     * @return array
     */
    protected function includedDirectories()
    {
        return $this->includeFilesAndDirectories->reject(function ($path) {
            return is_file($path);
        })->toArray();
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    protected function shouldExclude($path)
    {
        return $this->excludeFilesAndDirectories->contains(function ($key, $excludedPath) use ($path) {
            return starts_with($path, $excludedPath);
        });
    }

    /**
     * @param $paths
     *
     * @return \Illuminate\Support\Collection
     */
    protected function sanitize($paths)
    {
        return collect($paths)
            ->reject(function ($path) {
                return $path == '';
            })
            ->flatMap(function ($path) {
                return glob($path);
            })
            ->map(function ($path) {
                return realpath($path);
            })
            ->reject(function ($path) {
                return $path === false;
            });
    }
}
