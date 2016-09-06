<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

class FileSelection
{
    /** @var Collection */
    protected $includeFilesAndDirectories;

    /** @var Collection */
    protected $excludeFilesAndDirectories;

    /** @var bool */
    protected $shouldFollowLinks = false;

    /**
     * @param array|string $includeFilesAndDirectories
     *
     * @return \Spatie\Backup\Tasks\Backup\FileSelection
     */
    public static function create($includeFilesAndDirectories = []): FileSelection
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
    public function excludeFilesFrom($excludeFilesAndDirectories): FileSelection
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
    public function shouldFollowLinks(bool $shouldFollowLinks): FileSelection
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

    protected function includedFiles(): array
    {
        return $this->includeFilesAndDirectories->filter(function ($path) {
            return is_file($path);
        })->toArray();
    }

    protected function includedDirectories(): array
    {
        return $this->includeFilesAndDirectories->reject(function ($path) {
            return is_file($path);
        })->toArray();
    }

    protected function shouldExclude(string $path): bool
    {
        foreach ($this->excludeFilesAndDirectories as $excludedPath) {
            if (starts_with($path, $excludedPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|array $paths
     * @return \Illuminate\Support\Collection
     */
    protected function sanitize($paths): Collection
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
