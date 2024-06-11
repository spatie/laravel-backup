<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Config\SourceFilesConfig;
use Spatie\DbDumper\DbDumper;

class BackupJobFactory
{
    public static function createFromConfig(Config $config): BackupJob
    {
        return (new BackupJob($config))
            ->setFileSelection(static::createFileSelection($config->backup->source->files))
            ->setDbDumpers(static::createDbDumpers($config->backup->source->databases))
            ->setBackupDestinations(BackupDestinationFactory::createFromArray($config));
    }

    protected static function createFileSelection(SourceFilesConfig $sourceFiles): FileSelection
    {
        return FileSelection::create($sourceFiles->include)
            ->excludeFilesFrom($sourceFiles->exclude)
            ->shouldFollowLinks($sourceFiles->followLinks)
            ->shouldIgnoreUnreadableDirs($sourceFiles->ignoreUnreadableDirectories);
    }

    /**
     * @param  array<int, string>  $dbConnectionNames
     * @return Collection<string, DbDumper>
     */
    protected static function createDbDumpers(array $dbConnectionNames): Collection
    {
        return collect($dbConnectionNames)->mapWithKeys(
            fn (string $dbConnectionName): array => [$dbConnectionName => DbDumperFactory::createFromConnection($dbConnectionName)]
        );
    }
}
