<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class DestinationConfig extends Data
{
    /**
     * @param int<0,9> $compressionLevel
     * @param array<string> $disks
     */
    public function __construct(
        public int $compressionMethod,
        public int $compressionLevel,
        public string $filenamePrefix,
        public array $disks,
    ) {
    }
}
