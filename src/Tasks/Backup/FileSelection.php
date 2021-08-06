<?php

namespace Spatie\Backup\Tasks\Backup;

use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class FileSelection
{
    protected Collection $includeFilesAndDirectories;

    protected Collection $excludeFilesAndDirectories;

    protected bool $shouldFollowLinks = false;

    protected bool $shouldIgnoreUnreadableDirs = false;

    public static function create(array | string $includeFilesAndDirectories = []): self
    {
        return new static($includeFilesAndDirectories);
    }

    public function __construct(array | string $includeFilesAndDirectories = [])
    {
        $this->includeFilesAndDirectories = collect($includeFilesAndDirectories);

        $this->excludeFilesAndDirectories = collect();
    }

    public function excludeFilesFrom(array | string $excludeFilesAndDirectories): self
    {
        $this->excludeFilesAndDirectories = $this->excludeFilesAndDirectories->merge($this->sanitize($excludeFilesAndDirectories));

        return $this;
    }

    public function shouldFollowLinks(bool $shouldFollowLinks): self
    {
        $this->shouldFollowLinks = $shouldFollowLinks;

        return $this;
    }

    public function shouldIgnoreUnreadableDirs(bool $ignoreUnreadableDirs): self
    {
        $this->shouldIgnoreUnreadableDirs = $ignoreUnreadableDirs;

        return $this;
    }

    public function selectedFiles(): Generator | array
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
            return [];
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
        return $this
            ->includeFilesAndDirectories
            ->filter(fn ($path) => is_file($path))->toArray();
    }

    protected function includedDirectories(): array
    {
        return $this
            ->includeFilesAndDirectories
            ->reject(fn ($path) => is_file($path))->toArray();
    }

    protected function shouldExclude(string $path): bool
    {
        foreach ($this->excludeFilesAndDirectories as $excludedPath) {
            if (Str::startsWith(realpath($path), is_dir($excludedPath) ? rtrim($excludedPath, '/').'/' : $excludedPath)) {
                if (pathinfo($excludedPath, PATHINFO_EXTENSION) && realpath($path) != $excludedPath && is_file($excludedPath)) {
                    continue;
                }
                return true;
            }
        }

        return false;
    }

    protected function sanitize(string | array $paths): Collection
    {
        return collect($paths)
            ->reject(fn ($path) => $path === '')
            ->flatMap(fn ($path) => glob($path))
            ->map(fn ($path) => realpath($path))
            ->reject(fn ($path) => $path === false);
    }
}
