<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class SourceConfig extends Data
{
    /**
     * @param  array<string>  $databases
     */
    protected function __construct(
        public SourceFilesConfig $files,
        public array $databases,
    ) {}

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            files: SourceFilesConfig::fromArray($data['files']),
            databases: $data['databases'],
        );
    }
}
