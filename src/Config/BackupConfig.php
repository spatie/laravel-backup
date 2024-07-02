<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;

class BackupConfig extends Data
{
    protected function __construct(
        public string $name,
        public SourceConfig $source,
        public ?string $databaseDumpCompressor,
        public ?string $databaseDumpFileTimestampFormat,
        public string $databaseDumpFilenameBase,
        public string $databaseDumpFileExtension,
        public DestinationConfig $destination,
        public ?string $temporaryDirectory,
        public ?string $password,
        public string $encryption,
        public int $tries,
        public int $retryDelay,
        public ?MonitoredBackupsConfig $monitoredBackups,
    ) {
        if ($this->tries < 1) {
            throw InvalidConfig::integerMustBePositive('tries');
        }
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $monitoredBackups = $data['monitored_backups'] ?? $data['monitorBackups'] ?? null;

        return new self(
            name: $data['name'],
            source: SourceConfig::fromArray($data['source']),
            databaseDumpCompressor: $data['database_dump_compressor'] ?? null,
            databaseDumpFileTimestampFormat: $data['database_dump_file_timestamp_format'] ?? null,
            databaseDumpFilenameBase: $data['database_dump_filename_base'],
            databaseDumpFileExtension: $data['database_dump_file_extension'],
            destination: DestinationConfig::fromArray($data['destination']),
            temporaryDirectory: $data['temporary_directory'] ?? null,
            password: $data['password'],
            encryption: $data['encryption'],
            tries: $data['tries'],
            retryDelay: $data['retry_delay'],
            monitoredBackups: $monitoredBackups ? MonitoredBackupsConfig::fromArray($monitoredBackups) : null,
        );
    }
}
