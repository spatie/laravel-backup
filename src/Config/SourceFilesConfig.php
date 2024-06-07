<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class SourceFilesConfig extends Data
{
    /**
     * @param array<string> $include
     * @param array<string> $exclude
     * @param bool $followLinks
     * @param bool $ignoreUnreadableDirectories
     * @param string|null $relativePath
     */
    public function __construct(
        public array $include,
        public array $exclude,
        public bool $followLinks,
        public bool $ignoreUnreadableDirectories,
        public ?string $relativePath,
    ) {
    }
}
