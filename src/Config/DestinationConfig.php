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
     * @param  array<string>  $fallbackDisks
     */
    protected function __construct(
        public int $compressionMethod,
        public int $compressionLevel,
        public string $filenamePrefix,
        public array $disks,
        public array $fallbackDisks,
        public bool $enableFailover,
        public int $failoverRetries,
        public int $failoverDelay,
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
            fallbackDisks: $data['fallback_disks'] ?? [],
            enableFailover: $data['enable_failover'] ?? false,
            failoverRetries: $data['failover_retries'] ?? 3,
            failoverDelay: $data['failover_delay'] ?? 5,
        );
    }
}
