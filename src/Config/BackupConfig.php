<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Enums\DumpFilenameBase;
use Spatie\Backup\Enums\Encryption;
use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Support\Data;

class BackupConfig extends Data
{
    protected function __construct(
        public string $name,
        public SourceConfig $source,
        public ?string $databaseDumpCompressor,
        public ?string $databaseDumpFileTimestampFormat,
        public DumpFilenameBase $databaseDumpFilenameBase,
        public string $databaseDumpFileExtension,
        public DestinationConfig $destination,
        public ?string $temporaryDirectory,
        public ?string $password,
        public Encryption $encryption,
        public int $tries,
        public int $retryDelay,
        public ?MonitoredBackupsConfig $monitoredBackups,
        public bool $verifyBackup,
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
            databaseDumpFilenameBase: self::parseDumpFilenameBase($data['database_dump_filename_base'] ?? 'database'),
            databaseDumpFileExtension: $data['database_dump_file_extension'] ?? '',
            destination: DestinationConfig::fromArray($data['destination']),
            temporaryDirectory: $data['temporary_directory'] ?? null,
            password: $data['password'] ?? null,
            encryption: self::parseEncryption(array_key_exists('encryption', $data) ? $data['encryption'] : 'default'),
            tries: $data['tries'] ?? 1,
            retryDelay: $data['retry_delay'] ?? 0,
            monitoredBackups: $monitoredBackups ? MonitoredBackupsConfig::fromArray($monitoredBackups) : null,
            verifyBackup: $data['verify_backup'] ?? false,
        );
    }

    private static function parseDumpFilenameBase(string $value): DumpFilenameBase
    {
        return DumpFilenameBase::from($value);
    }

    private static function parseEncryption(mixed $value): Encryption
    {
        if ($value === null || $value === false) {
            return Encryption::None;
        }

        if ($value instanceof Encryption) {
            return $value;
        }

        if (is_string($value)) {
            return Encryption::from($value);
        }

        return Encryption::None;
    }
}
