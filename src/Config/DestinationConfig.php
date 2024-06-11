<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;

class DestinationConfig extends Data
{
    /**
     * @param  int<0,9>  $compressionLevel
     * @param  array<string>  $disks
     */
    protected function __construct(
        public int $compressionMethod,
        public int $compressionLevel,
        public string $filenamePrefix,
        public array $disks,
    ) {
        if ($compressionLevel > 9) {
            throw InvalidConfig::integerMustBeBetween('compression_level', 0, 9);
        }

        if ($compressionLevel < 0) {
            throw InvalidConfig::integerMustBeBetween('compression_level', 0, 9);
        }
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            compressionMethod: $data['compression_method'],
            compressionLevel: $data['compression_level'],
            filenamePrefix: $data['filename_prefix'],
            disks: $data['disks'],
        );
    }
}
