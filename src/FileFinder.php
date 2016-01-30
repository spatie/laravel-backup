<?php

namespace Spatie\Backup;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class FileFinder
{
    /**
     * @var array
     */
    protected $includeFilesAndDirectories;
    /**
     * @var array
     */
    protected $excludeFilesAndDirectories;

    public function __construct(array $includeFilesAndDirectories, array $excludeFilesAndDirectories)
    {
        $this->includeFilesAndDirectories = $includeFilesAndDirectories;
        $this->excludeFilesAndDirectories = $excludeFilesAndDirectories;
    }

    public function getSelectedFiles() : array
    {
        if (count($this->includeFilesAndDirectories) === 0) {
            return [];
        }

        $filesToBeIncluded = $this->getAllFiles($this->includeFilesAndDirectories);

        if (count($this->excludeFilesAndDirectories) === 0) {
            return $filesToBeIncluded;
        }

        $filesToBeExcluded = $this->getAllFiles($this->excludeFilesAndDirectories);

        return collect($filesToBeIncluded)->filter(function ($file) use ($filesToBeExcluded) {
            return !in_array($file, $filesToBeExcluded);
        })
        ->toArray();
    }

    /*
     * Make a unique array of all files from a given array of files and directories.
     */
    protected function getAllFiles(array $files) : array
    {
        collect($files)
            ->filter(function (string $file) {
                return Filesystem::isFile($file) || Filesystem::isDirectory($file);
            })

            ->reduce(function (Collection $allFiles, string $file) {
                if (Filesystem::isDirectory($file)) {
                    return $allFiles->merge($this->getAllFilesFromDirectory($file));
                }

                return $allFiles->push($file);
            }, collect())

            ->map(function (string $file) {
                return pathinfo($file, PATHINFO_FILENAME);
            })

            ->unique()
            ->toArray();
    }

    /*
     * Recursively get all the files within a given directory.
     */
    protected function getAllFilesFromDirectory(string $directory) : array
    {
        $finder = (new Finder())
            ->ignoreDotFiles(false)
            ->ignoreVCS(false)
            ->files()
            ->in($directory);

        return iterator_to_array($finder);
    }
}
