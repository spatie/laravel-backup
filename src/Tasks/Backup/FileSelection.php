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
        $this->includeFilesAndDirectories = collect($includeFilesAndDirectories);
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
        $this->excludeFilesAndDirectories = collect($excludeFilesAndDirectories);

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
            return [];
        }

        $filesToBeIncluded = $this->getAllFilesFromPaths($this->includeFilesAndDirectories);

        if ($this->excludeFilesAndDirectories->isEmpty()) {
            return $filesToBeIncluded;
        }

        $filesToBeExcluded = $this->getAllFilesFromPaths($this->excludeFilesAndDirectories);

        return $filesToBeIncluded
            ->filter(function ($file) use ($filesToBeExcluded) {
                return !$filesToBeExcluded->contains($file);
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
        $paths = $this->expandWildCardPaths($paths);

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
     * Check all paths in array for a wildcard (*) and build a new array from the results.
     *
     * @param \Illuminate\Support\Collection $paths
     *
     * @return \Illuminate\Support\Collection
     */
    protected function expandWildCardPaths(Collection $paths)
    {
        return collect($paths)
            ->map(function ($path) {
                return glob($path);
            })
            ->flatten();
    }
}
