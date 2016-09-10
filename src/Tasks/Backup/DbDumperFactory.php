<?php

namespace Spatie\Backup\Tasks\Backup;

use Spatie\Backup\Exceptions\CannotCreateDbDumper;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\DbDumper;

class DbDumperFactory
{
    /**
     * @param string $dbConnectionName ;
     *
     * @return mixed
     */
    public static function create(string $dbConnectionName)
    {
        $dbConfig = config("database.connections.{$dbConnectionName}");

        $dbDumper = static::getDumper($dbConfig['driver'])
            ->setHost($dbConfig['host'])
            ->setDbName($dbConfig['database'])
            ->setUserName($dbConfig['username'])
            ->setPassword($dbConfig['password']);

        if (isset($dbConfig['dump'])) {
            $dbDumper = static::processExtraDumpParameters($dbConfig['dump'], $dbDumper);
        }

        return $dbDumper;
    }

    public static function getDumper($dbDriver): DbDumper
    {
        $driver = strtolower($dbDriver);

        if ($driver === 'mysql') {
            return new MySql();
        }

        if ($driver === 'pgsql') {
            return new PostgreSql();
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
    protected static function processExtraDumpParameters(array $dumpConfiguration, $dbDumper)
    {
        collect($dumpConfiguration)->each(function ($configValue, $configName) use ($dbDumper) {
            $methodName = studly_case(is_numeric($configName) ? $configValue : $configName);
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
        if (is_null($methodValue)) {
            $dbDumper->$methodName();

            return $dbDumper;
        }

        $dbDumper->$methodName($methodValue);

        return $dbDumper;
    }

    protected static function determineValidMethodName(DbDumper $dbDumper, string $methodName): string
    {
        return collect([$methodName, 'set_'.$methodName])
            ->first(function (string $methodName) use ($dbDumper) {
                return method_exists($dbDumper, $methodName);
            }, '');
    }
}
