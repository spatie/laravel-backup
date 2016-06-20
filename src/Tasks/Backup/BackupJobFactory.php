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
    public static function createFromArray(array $config, $options = [])
    {   


        $backupJob = (new BackupJob())
            ->setFileSelection(static::getFileSelection($config['backup']['source']['files']))
            ->setDbDumpers(static::getDbDumpers($config['backup']['source']['databases'] , $options) )
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
            ->excludeFilesFrom($sourceFiles['exclude'])
            ->shouldFollowLinks(isset($sourceFiles['followLinks']) && $sourceFiles['followLinks']);
    }

    /**
     * @param array $dbConnectionNames
     *
     * @return array
     */
    protected static function getDbDumpers(array $dbConnectionNames , $parametersOptions)
    {

        $dbDumpers = [];
        
        foreach( $dbConnectionNames as $dbConnectionName => $options )
        {

            //To fits when there are no options
            if( ! is_array($options) )
            {
                $dbConnectionName = $options;
                $options = [];
            }

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

                    if( array_key_exists('exclude-tables', $parametersOptions) )
                    {
                        $excludeTables = static::prefixDbName( $dbConfig['database'] , $parametersOptions['exclude-tables'] );
                        
                        $dbDumper->excludeTables( $excludeTables );
                    }
                    else if( array_key_exists('excludeTables', $options) )
                    {
                        #prepend database name
                        $excludeTables = static::prefixDbName( $dbConfig['database'] , $options['excludeTables'] );
                        
                        $dbDumper->excludeTables( $excludeTables );
                    }
                    
                    if( array_key_exists('includeTables', $options) )
                    {
                        #prepend database name
                        $includeTables =  static::prefixDbName( $dbConfig['database'] , $options['includeTables'] );
                        
                        $dbDumper->includeTables( $includeTables );
                    }


                    $dbDumpers[] = $dbDumper;
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

                    $dbDumpers[] = $dbDumper;
                    break;

                default :
                    throw InvalidConfiguration::cannotUseUnsupportedDriver($dbConnectionName, $dbConfig['driver']);
                    break;
            }
         }

        return $dbDumpers;
    }

    private static function prefixDbName( $dbName , $tables )
    {
        return array_map(function($value) use ($dbName) { return $dbName.'.'.$value; }, $tables);
    }
}
