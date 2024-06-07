<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class BackupConfig extends Data
{
    /**
     * @param positive-int $tries
     */
    public function __construct(
        public string $name,
        public SourceConfig $source,
        public ?string $databaseDumpCompressor,
        public ?string $databaseDumpFileTimestampFormat,
        public string $databaseDumpFilenameBase,
        public string $databaseDumpFileExtension,
        public DestinationConfig $destination,
        public string $temporaryDirectory,
        public ?string $password,
        public string $encryption,
        public int $tries,
        public int $retryDelay,
    ) {
    }
}
