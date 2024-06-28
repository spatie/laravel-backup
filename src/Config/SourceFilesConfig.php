<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class SourceFilesConfig extends Data
{
    /**
     * @param  array<string>  $include
     * @param  array<string>  $exclude
     */
    protected function __construct(
        public array $include,
        public array $exclude,
        public bool $followLinks,
        public bool $ignoreUnreadableDirectories,
        public ?string $relativePath,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            include: $data['include'],
            exclude: $data['exclude'],
            followLinks: $data['follow_links'] ?? false,
            ignoreUnreadableDirectories: $data['ignore_unreadable_directories'] ?? false,
            relativePath: $data['relative_path'],
        );
    }
}
