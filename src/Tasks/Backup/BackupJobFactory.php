<?php

namespace Spatie\Backup\Tasks\Backup;

use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Exceptions\InvalidConfiguration;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;

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

    protected static function getDbDumpers(array $dbConnectionNames): array
    {
        $dbDumpers = array_map(function ($dbConnectionName) {
            $dbConfig = config("database.connections.{$dbConnectionName}");

            switch ($dbConfig['driver']) {
                case 'mysql':
                    $dbDumper = MySql::create()
                        ->setHost($dbConfig['host'])
                        ->setDbName($dbConfig['database'])
                        ->setUserName($dbConfig['username'])
                        ->setPassword($dbConfig['password'])
                        ->setDumpBinaryPath(isset($dbConfig['dump_command_path']) ? $dbConfig['dump_command_path'] : '')
                        ->setTimeout(isset($dbConfig['dump_command_timeout']) ? $dbConfig['dump_command_timeout'] : null);

                    if (isset($dbConfig['port'])) {
                        $dbDumper->setPort($dbConfig['port']);
                    }

                    if (isset($dbConfig['dump_using_single_transaction']) && $dbConfig['dump_using_single_transaction'] == true) {
                        $dbDumper->useSingleTransaction();
                    }

                    return $dbDumper;
                    break;

                case 'pgsql':
                    $dbDumper = PostgreSql::create()
                        ->setHost($dbConfig['host'])
                        ->setDbName($dbConfig['database'])
                        ->setUserName($dbConfig['username'])
                        ->setPassword($dbConfig['password'])
                        ->setDumpBinaryPath(isset($dbConfig['dump_command_path']) ? $dbConfig['dump_command_path'] : '')
                        ->setTimeout(isset($dbConfig['dump_command_timeout']) ? $dbConfig['dump_command_timeout'] : null);

                    if (isset($dbConfig['dump_use_inserts']) && $dbConfig['dump_use_inserts'] == true) {
                        $dbDumper->useInserts();
                    }

                    if (isset($dbConfig['port'])) {
                        $dbDumper->setPort($dbConfig['port']);
                    }

                    return $dbDumper;
                    break;

                default:
                    throw InvalidConfiguration::cannotUseUnsupportedDriver($dbConnectionName, $dbConfig['driver']);
                    break;
            }
        }, $dbConnectionNames);

        return $dbDumpers;
    }
}
