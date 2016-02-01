<?php

namespace Spatie\Backup;

use Spatie\Backup\Exceptions\InvalidConfiguration;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\DbDumper;

class BackupJobFactory
{
    public static function createFromArray(array $config) : BackupJob
    {
        $backupJob = (new BackupJob())
            ->setFileSelections(self::getFileSelection($config['source']['files']))
            ->setDbDumpers(self::getDbDumpers($config['source']['databases']))
            ->setBackupDestinations(self::getBackupDestinations($config['destination']));

        return $backupJob;
    }

    protected static function getFileSelection(array $sourceFiles) : FileSelection
    {
        return (new FileSelection($sourceFiles['include']))
            ->excludeFilesFrom($sourceFiles['exclude']);
    }

    protected static function getDbDumpers(array $dbConnectionNames) : DbDumper
    {
        $dbDumpers = array_map(function (string $dbConnectionName) {

            $dbConfig = config("database.connections.{$dbConnectionName}");

            if ($dbConfig['driver'] != 'mysql') {
                throw InvalidConfiguration::cannotUseUnsupportedDriver($dbConnectionName, $dbConfig['driver']);
            }

            return MySql::create()
                ->setHost($dbConfig['host'])
                ->setDbName($dbConfig['database'])
                ->setUserName($dbConfig['username'])
                ->setPassword($dbConfig['password']);

        }, $dbConnectionNames);

        return $dbDumpers;
    }

    protected static function getBackupDestinations(array $destinationConfig) : array
    {
        $backupDestinations = array_map(function (string $filesystemName) use ($destinationConfig) {
            return BackupDestination::create($filesystemName, $destinationConfig['path']);
        }, $destinationConfig['filesystems']);

        return $backupDestinations;
    }
}
