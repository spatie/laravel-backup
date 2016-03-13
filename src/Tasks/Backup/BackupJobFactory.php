<?php

namespace Spatie\Backup\Tasks\Backup;

use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Exceptions\InvalidConfiguration;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;

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

            switch ($dbConfig['driver']) {
                case 'mysql':
                    return MySql::create()
                        ->setHost($dbConfig['host'])
                        ->setDbName($dbConfig['database'])
                        ->setUserName($dbConfig['username'])
                        ->setPassword($dbConfig['password']);
                    break;

                case 'pgsql':
                    return PostgreSql::create()
                        ->setHost($dbConfig['host'])
                        ->setDbName($dbConfig['database'])
                        ->setUserName($dbConfig['username'])
                        ->setPassword($dbConfig['password']);
                    break;

                default:
                    throw InvalidConfiguration::cannotUseUnsupportedDriver($dbConnectionName, $dbConfig['driver']);
                    break;
            }
        }, $dbConnectionNames);

        return $dbDumpers;
    }
}
