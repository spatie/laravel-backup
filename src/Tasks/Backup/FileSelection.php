<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class FileSelection
{
    /** @var \Illuminate\Support\Collection */
    protected $includeFilesAndDirectories;

    /** @var \Illuminate\Support\Collection */
    protected $excludeFilesAndDirectories;

    /** @var bool */
    protected $shouldFollowLinks = false;

    /** @var bool */
    protected $shouldIgnoreUnreadableDirs = false;

    /**
     * @param array|string $includeFilesAndDirectories
     *
     * @return \Spatie\Backup\Tasks\Backup\FileSelection
     */
    public static function create($includeFilesAndDirectories = []): self
    {
        return new static($includeFilesAndDirectories);
    }

    /**
     * @param array|string $includeFilesAndDirectories
     */
    public function __construct($includeFilesAndDirectories = [])
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
    public function excludeFilesFrom($excludeFilesAndDirectories): self
    {
        $this->excludeFilesAndDirectories = $this->excludeFilesAndDirectories->merge($this->sanitize($excludeFilesAndDirectories));

        return $this;
    }

    public function shouldFollowLinks(bool $shouldFollowLinks): self
    {
        $this->shouldFollowLinks = $shouldFollowLinks;

        return $this;
    }

    /**
     * Set if it should ignore the unreadable directories.
     *
     * @param bool $ignoreUnreadableDirs
     *
     * @return \Spatie\Backup\Tasks\Backup\FileSelection
     */
    public function shouldIgnoreUnreadableDirs(bool $ignoreUnreadableDirs): self
    {
        $this->shouldIgnoreUnreadableDirs = $ignoreUnreadableDirs;

        return $this;
    }

    /**
     * @return \Generator|string[]
     */
    public function selectedFiles()
    {
        if ($this->includeFilesAndDirectories->isEmpty()) {
            return [];
        }

        $finder = (new Finder())
            ->ignoreDotFiles(false)
            ->ignoreVCS(false);

        if ($this->shouldFollowLinks) {
            $finder->followLinks();
        }

        if ($this->shouldIgnoreUnreadableDirs) {
            $finder->ignoreUnreadableDirs();
        }

        foreach ($this->includedFiles() as $includedFile) {
            yield $includedFile;
        }

        if (! count($this->includedDirectories())) {
            return;
        }

        $finder->in($this->includedDirectories());

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
        $path = realpath($path);
        if (is_dir($path)) {
            $path .= '/';
        }
        foreach ($this->excludeFilesAndDirectories as $excludedPath) {
            if (Str::startsWith($path, $excludedPath.(is_dir($excludedPath) ? '/' : ''))) {
                if ($path != $excludedPath && is_file($excludedPath)) {
                    continue;
                }
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|array $paths
     *
     * @return \Illuminate\Support\Collection
     */
    protected function sanitize($paths): Collection
    {
        return collect($paths)
            ->reject(function ($path) {
                return $path === '';
            })
            ->flatMap(function ($path) {
                return glob(str_replace('*', '{.[!.],}*', $path), GLOB_BRACE);
            })
            ->map(function ($path) {
                return realpath($path);
            })
            ->reject(function ($path) {
                return $path === false;
            });
    }
}
