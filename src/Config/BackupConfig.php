<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Support\Data;

class BackupConfig extends Data
{
    /**
     * @param positive-int $tries
     */
    protected function __construct(
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
        public ?NotificationsConfig $notifications,
        public ?MonitoredBackupsConfig $monitoredBackups,
    ) {
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        $monitoredBackups = $data['monitored_backups'] ?? $data['monitorBackups'] ?? null;

        return new self(
            name: $data['name'],
            source: SourceConfig::fromArray($data['source']),
            databaseDumpCompressor: $data['database_dump_compressor'],
            databaseDumpFileTimestampFormat: $data['database_dump_file_timestamp_format'],
            databaseDumpFilenameBase: $data['database_dump_filename_base'],
            databaseDumpFileExtension: $data['database_dump_file_extension'],
            destination: DestinationConfig::fromArray($data['destination']),
            temporaryDirectory: $data['temporary_directory'],
            password: $data['password'],
            encryption: $data['encryption'],
            tries: $data['tries'],
            retryDelay: $data['retry_delay'],
            notifications: isset($data['notifications']) ? NotificationsConfig::fromArray($data['notifications']) : null,
            monitoredBackups: $monitoredBackups ? MonitoredBackupsConfig::fromArray($monitoredBackups) : null,
        );
    }
}
