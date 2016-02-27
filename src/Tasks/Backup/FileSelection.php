<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FileSelection
{
    /** @var array */
    protected $includeFilesAndDirectories = [];

    /** @var array */
    protected $excludeFilesAndDirectories = [];

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
        if (!is_array($includeFilesAndDirectories)) {
            $includeFilesAndDirectories = [$includeFilesAndDirectories];
        }

        $this->includeFilesAndDirectories = $includeFilesAndDirectories;
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
        if (!is_array($excludeFilesAndDirectories)) {
            $excludeFilesAndDirectories = [$excludeFilesAndDirectories];
        }

        $this->excludeFilesAndDirectories = $excludeFilesAndDirectories;

        return $this;
    }

    /**
     * @return array
     */
    public function getSelectedFiles()
    {
        if (count($this->includeFilesAndDirectories) === 0) {
            return [];
        }

        $filesToBeIncluded = $this->getAllFilesFromPaths($this->includeFilesAndDirectories);

        if (count($this->excludeFilesAndDirectories) === 0) {
            return $filesToBeIncluded;
        }

        $filesToBeExcluded = $this->getAllFilesFromPaths($this->excludeFilesAndDirectories);

        $selectedFiles = collect($filesToBeIncluded)
            ->filter(function ($file) use ($filesToBeExcluded) {
                return !in_array($file, $filesToBeExcluded);
            })
            ->toArray();

        $selectedFiles = array_values($selectedFiles);

        return $selectedFiles;
    }

    /**
     * Make a unique array of all files from a given array of files and directories.
     *
     * @param array $paths
     *
     * @return array
     */
    protected function getAllFilesFromPaths(array $paths)
    {
        $allFiles = collect($paths)
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

            ->unique()
            ->toArray();

        return $allFiles;
    }

    /**
     * Recursively get all the files within a given directory.
     *
     * @param string $directory
     *
     * @return array
     */
    protected function getAllFilesFromDirectory($directory)
    {
        $finder = (new Finder())
            ->ignoreDotFiles(false)
            ->ignoreVCS(false)
            ->files()
            ->in($directory);

        $filePaths = array_map(function (SplFileInfo $fileInfo) {
            return $fileInfo->getPathname();
        }, iterator_to_array($finder));

        $filePaths = array_values($filePaths);

        return $filePaths;
    }
}
