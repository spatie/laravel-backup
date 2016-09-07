<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Collection;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class BackupJobFactory
{
    public static function createFromArray(array $config): BackupJob
    {
        $backupJob = (new BackupJob())
            ->setFileSelection(static::getFileSelection($config['backup']['source']['files']))
            ->setDbDumpers(static::getDbDumpers($config['backup']['source']['databases']))
            ->setBackupDestinations(BackupDestinationFactory::createFromArray($config['backup']));

        return $backupJob;
    }

    protected static function getFileSelection(array $sourceFiles): FileSelection
    {
        return (new FileSelection($sourceFiles['include']))
            ->excludeFilesFrom($sourceFiles['exclude'])
            ->shouldFollowLinks(isset($sourceFiles['followLinks']) && $sourceFiles['followLinks']);
    }

    protected static function getDbDumpers(array $dbConnectionNames): Collection
    {
        return collect($dbConnectionNames)->map(function (string $dbConnectionName) {
            return DbDumperFactory::create($dbConnectionName);
        });
    }
}
