<?php

namespace Spatie\Backup\Tasks\Backup;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\Sqlite;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\Backup\Exceptions\CannotCreateDbDumper;

class DbDumperFactory
{
    /**
     * @param string $dbConnectionName
     *
     * @return \Spatie\DbDumper\DbDumper
     */
    public static function createFromConnection(string $dbConnectionName): DbDumper
    {
        $dbConfig = config("database.connections.{$dbConnectionName}");

        if (isset($dbConfig['read'])) {
            $dbConfig = array_except(
                array_merge($dbConfig, $dbConfig['read']),
                ['read', 'write']
            );
        }

        $dbDumper = static::forDriver($dbConfig['driver'])
            ->setHost(array_first(array_wrap($dbConfig['host'] ?? '')))
            ->setDbName($dbConfig['database'])
            ->setUserName($dbConfig['username'] ?? '')
            ->setPassword($dbConfig['password'] ?? '');

        if ($dbDumper instanceof MySql) {
            $dbDumper->setDefaultCharacterSet($dbConfig['charset'] ?? '');
        }

        if (isset($dbConfig['port'])) {
            $dbDumper = $dbDumper->setPort($dbConfig['port']);
        }

        if (isset($dbConfig['dump'])) {
            $dbDumper = static::processExtraDumpParameters($dbConfig['dump'], $dbDumper);
        }

        return $dbDumper;
    }

    protected static function forDriver($dbDriver): DbDumper
    {
        $driver = strtolower($dbDriver);

        if ($driver === 'mysql' || $driver === 'mariadb') {
            return new MySql();
        }

        if ($driver === 'pgsql') {
            return new PostgreSql();
        }

        if ($driver === 'sqlite') {
            return new Sqlite();
        }

        if ($driver === 'mongodb') {
            return new MongoDb();
        }

        throw CannotCreateDbDumper::unsupportedDriver($driver);
    }

    /**
     * @param array $dumpConfiguration
     *
     * @param $dbDumper
     *
     * @return mixed
     */
    protected static function processExtraDumpParameters(array $dumpConfiguration, $dbDumper): DbDumper
    {
        collect($dumpConfiguration)->each(function ($configValue, $configName) use ($dbDumper) {
            $methodName = lcfirst(studly_case(is_numeric($configName) ? $configValue : $configName));
            $methodValue = is_numeric($configName) ? null : $configValue;

            $methodName = static::determineValidMethodName($dbDumper, $methodName);

            if (method_exists($dbDumper, $methodName)) {
                static::callMethodOnDumper($dbDumper, $methodName, $methodValue);
            }
        });

        return $dbDumper;
    }

    /**
     * @param \Spatie\DbDumper\DbDumper $dbDumper
     * @param string $methodName
     * @param string|null $methodValue
     *
     * @return \Spatie\DbDumper\DbDumper
     */
    protected static function callMethodOnDumper(DbDumper $dbDumper, string $methodName, $methodValue): DbDumper
    {
        if (! $methodValue) {
            $dbDumper->$methodName();

            return $dbDumper;
        }

        $dbDumper->$methodName($methodValue);

        return $dbDumper;
    }

    protected static function determineValidMethodName(DbDumper $dbDumper, string $methodName): string
    {
        return collect([$methodName, 'set'.ucfirst($methodName)])
            ->first(function (string $methodName) use ($dbDumper) {
                return method_exists($dbDumper, $methodName);
            }, '');
    }
}
