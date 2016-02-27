<?php

namespace Spatie\Backup\Tasks\Backup;

use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Exceptions\InvalidConfiguration;
use Spatie\DbDumper\Databases\MySql;

class BackupJobFactory
{
    /**
     * @param array $config
     *
     * @return \Spatie\Backup\Tasks\Backup\BackupJob
     */
    public static function createFromArray(array $config)
    {
        $backupJob = (new BackupJob())
            ->setFileSelection(static::getFileSelection($config['backup']['source']['files']))
            ->setDbDumpers(static::getDbDumpers($config['backup']['source']['databases']))
            ->setBackupDestinations(BackupDestinationFactory::createFromArray($config['backup']));

        return $backupJob;
    }

    /**
     * @param array $sourceFiles
     *
     * @return \Spatie\Backup\Tasks\Backup\FileSelection
     */
    protected static function getFileSelection(array $sourceFiles)
    {
        return (new FileSelection($sourceFiles['include']))
            ->excludeFilesFrom($sourceFiles['exclude']);
    }

    /**
     * @param array $dbConnectionNames
     *
     * @return array
     */
    protected static function getDbDumpers(array $dbConnectionNames)
    {
        $dbDumpers = array_map(function ($dbConnectionName) {

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
