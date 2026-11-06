<?php

namespace Spatie\Backup\Tasks\Backup;

use Generator;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

class FileSelection
{
    /** @var Collection<int, string> */
    protected Collection $includeFilesAndDirectories;

    /** @var Collection<int, string> */
    protected Collection $excludeFilesAndDirectories;

    protected bool $shouldFollowLinks = false;

    protected bool $shouldIgnoreUnreadableDirs = false;

    /** @var array<int, array{path: string, prefix: string, isFile: bool}>|null */
    protected ?array $resolvedExclusions = null;

    /** @param array<string>|string $includeFilesAndDirectories */
    public static function create(array|string $includeFilesAndDirectories = []): static
    {
        return new static($includeFilesAndDirectories);
    }

    /** @param array<string>|string $includeFilesAndDirectories */
    public function __construct(array|string $includeFilesAndDirectories = [])
    {
        $this->includeFilesAndDirectories = collect($includeFilesAndDirectories);

        $this->excludeFilesAndDirectories = collect();
    }

    /** @param array<string>|string $excludeFilesAndDirectories */
    public function excludeFilesFrom(array|string $excludeFilesAndDirectories): self
    {
        $this->excludeFilesAndDirectories = $this->excludeFilesAndDirectories->merge($this->sanitize($excludeFilesAndDirectories));

        $this->resolvedExclusions = null;

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

    /** @return Generator|array<empty> */
    public function selectedFiles(): Generator|array
    {
        $this->resolvedExclusions = null;

        if ($this->includeFilesAndDirectories->isEmpty()) {
            return [];
        }

        $finder = (new Finder)
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

        $directories = $this->includedDirectories();

        if ($directories === []) {
            return [];
        }

        $finder->in($directories);

        foreach ($finder->getIterator() as $file) {
            if ($this->shouldExclude($file->getPathname())) {
                continue;
            }

            yield $file->getPathname();
        }
    }

    /** @return array<string> */
    protected function includedFiles(): array
    {
        return $this
            ->includeFilesAndDirectories
            ->filter(fn (string $path) => is_file($path))->toArray();
    }

    /** @return array<string> */
    protected function includedDirectories(): array
    {
        return $this
            ->includeFilesAndDirectories
            ->filter(function (string $path) {
                if (is_file($path)) {
                    return false;
                }

                if (! file_exists($path)) {
                    backupLogger()->warn("Include path `{$path}` does not exist, skipping.");

                    return false;
                }

                return true;
            })
            ->toArray();
    }

    /** @return array<int, array{path: string, prefix: string, isFile: bool}> */
    protected function resolveExclusions(): array
    {
        return $this->excludeFilesAndDirectories
            ->map(fn (string $excludedPath) => [
                'path' => $excludedPath,
                'prefix' => $excludedPath.(is_dir($excludedPath) ? DIRECTORY_SEPARATOR : ''),
                'isFile' => is_file($excludedPath),
            ])
            ->values()
            ->all();
    }

    protected function shouldExclude(string $path): bool
    {
        $exclusions = $this->resolvedExclusions ??= $this->resolveExclusions();

        $path = (string) realpath($path);

        if (is_dir($path)) {
            $path .= DIRECTORY_SEPARATOR;
        }

        foreach ($exclusions as $exclusion) {
            if (str_starts_with($path, $exclusion['prefix'])) {
                if ($path !== $exclusion['path'] && $exclusion['isFile']) {
                    continue;
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param  string|array<string>  $paths
     */
    protected function sanitize(string|array $paths): Collection
    {
        return collect($paths)
            ->reject(fn (string $path) => $path === '')
            ->flatMap(fn (string $path) => $this->getMatchingPaths($path))
            ->map(fn (string $path) => realpath($path))
            ->reject(fn ($path) => $path === false);
    }

    /** @return array<string> */
    protected function getMatchingPaths(string $path): array
    {
        if ($this->canUseGlobBrace($path)) {
            $result = @glob(str_replace('*', '{.[!.],}*', $path), GLOB_BRACE);

            if ($result !== false) {
                return $result;
            }
        }

        return glob($path) ?: [];
    }

    protected function canUseGlobBrace(string $path): bool
    {
        return str_contains($path, '*') && defined('GLOB_BRACE');
    }
}
