<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
        $this->includeFilesAndDirectories = $this->createPathCollection($includeFilesAndDirectories);
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
        $this->excludeFilesAndDirectories = $this->createPathCollection($excludeFilesAndDirectories);

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
     * @return \Illuminate\Support\Collection
     */
    public function getSelectedFiles()
    {
        if ($this->includeFilesAndDirectories->isEmpty()) {
            return collect();
        }

        $filesToBeIncluded = $this->getAllFilesFromPaths($this->includeFilesAndDirectories);

        if ($this->excludeFilesAndDirectories->isEmpty()) {
            return $filesToBeIncluded;
        }

        return $filesToBeIncluded
            ->reject(function ($path) {
                return $this->excludeFilesAndDirectories
                    ->contains(function ($key, $excludedPath) use ($path) {
                        return starts_with($path, $excludedPath);
                    });
            })
            ->values();
    }

    /**
     * Make a unique array of all files from a given array of files and directories.
     *
     * @param \Illuminate\Support\Collection $paths
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getAllFilesFromPaths(Collection $paths)
    {
        return $paths
            ->filter(function ($path) {
                return file_exists($path);
            })
            ->map(function ($file) {
                return realpath($file);
            })
            ->reduce(function (Collection $filePaths, $path) {
                if (is_dir($path)) {
                    return $filePaths->merge($this->getAllFilesFromDirectory($path));
                }

                return $filePaths->push($path);
            }, collect())
            ->unique();
    }

    /**
     * Recursively get all the files within a given directory.
     *
     * @param string $directory
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getAllFilesFromDirectory($directory)
    {
        $finder = (new Finder())
            ->ignoreDotFiles(false)
            ->ignoreVCS(false)
            ->files()
            ->in($directory);

        if ($this->shouldFollowLinks) {
            $finder->followLinks();
        }

        return collect(iterator_to_array($finder))
            ->map(function (SplFileInfo $fileInfo) {
                return $fileInfo->getPathname();
            })
            ->values();
    }

    /**
     * Fully expand paths, and reject non-existing paths.
     *
     * @param $paths
     *
     * @return \Illuminate\Support\Collection
     */
    protected function createPathCollection($paths)
    {
        return collect($paths)
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
