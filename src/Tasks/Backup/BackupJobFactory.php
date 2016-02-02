<?php

namespace Spatie\Backup\Tasks\Backup;

use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Exceptions\InvalidConfiguration;
use Spatie\DbDumper\Databases\MySql;

class BackupJobFactory
{
    public static function createFromArray(array $config) : BackupJob
    {
        $backupJob = (new BackupJob())
            ->setFileSelection(self::getFileSelection($config['backup']['source']['files']))
            ->setDbDumpers(self::getDbDumpers($config['backup']['source']['databases']))
            ->setBackupDestinations(BackupDestinationFactory::createFromArray($config['backup']['destination']));

        return $backupJob;
    }

    protected static function getFileSelection(array $sourceFiles) : FileSelection
    {
        return (new FileSelection($sourceFiles['include']))
            ->excludeFilesFrom($sourceFiles['exclude']);
    }

    protected static function getDbDumpers(array $dbConnectionNames) : array
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
}
