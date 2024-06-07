<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class SourceConfig extends Data
{
    /**
     * @param array<string> $databases
     */
    public function __construct(
        public SourceFilesConfig $files,
        public array $databases,
    ) {
    }
}
