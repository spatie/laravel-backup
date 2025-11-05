<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;
use ZipArchive;

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
        public bool $continueOnFailure,
    ) {
        if ($compressionLevel > 9) {
            throw InvalidConfig::integerMustBeBetween('compression_level', 0, 9);
        }

        if ($compressionLevel < 0) {
            throw InvalidConfig::integerMustBeBetween('compression_level', 0, 9);
        }
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            compressionMethod: $data['compression_method'] ?? ZipArchive::CM_DEFAULT,
            compressionLevel: $data['compression_level'] ?? 9,
            filenamePrefix: $data['filename_prefix'] ?? '',
            disks: $data['disks'] ?? ['local'],
            continueOnFailure: $data['continue_on_failure'] ?? false,
        );
    }
}
