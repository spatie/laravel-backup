<?php

namespace Spatie\Backup\Tasks\Backup;

use Exception;
use Illuminate\Database\ConfigurationUrlParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Backup\Exceptions\CannotCreateDbDumper;
use Spatie\DbDumper\Databases\MariaDb;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;
use Spatie\DbDumper\DbDumper;

class DbDumperFactory
{
    /** @var array<DbDumper> */
    protected static array $custom = [];

    public static function createFromConnection(string $dbConnectionName): DbDumper
    {
        $parser = new ConfigurationUrlParser;

        if (config("database.connections.{$dbConnectionName}") === null) {
            throw CannotCreateDbDumper::unsupportedDriver($dbConnectionName);
        }

        try {
            $dbConfig = $parser->parseConfiguration(config("database.connections.{$dbConnectionName}"));
        } catch (Exception) {
            throw CannotCreateDbDumper::unsupportedDriver($dbConnectionName);
        }

        if (isset($dbConfig['read'])) {
            $dbConfig = Arr::except(
                array_merge($dbConfig, $dbConfig['read']),
                ['read', 'write']
            );
        }

        $dbDumper = static::forDriver($dbConfig['driver'] ?? '')
            ->setHost(Arr::first(Arr::wrap($dbConfig['host'] ?? '')))
            ->setDbName($dbConfig['connect_via_database'] ?? $dbConfig['database'])
            ->setUserName($dbConfig['username'] ?? '')
            ->setPassword($dbConfig['password'] ?? '');

        if ($dbDumper instanceof MySql) {
            $dbDumper
                ->setSkipSsl($dbConfig['dump']['skip_ssl'] ?? false)
                ->setDefaultCharacterSet($dbConfig['charset'] ?? '')
                ->setGtidPurged($dbConfig['dump']['mysql_gtid_purged'] ?? 'AUTO');
        }

        if ($dbDumper instanceof MongoDb) {
            $dbDumper->setAuthenticationDatabase($dbConfig['dump']['mongodb_user_auth'] ?? '');
        }

        if (isset($dbConfig['port'])) {
            if (filter_var($dbConfig['port'], FILTER_VALIDATE_INT, [
                'options' => [
                    'min_range' => 1,
                    'max_range' => 65535,
                ],
            ]) !== false) {
                $dbDumper = $dbDumper->setPort((int) $dbConfig['port']);
            }
        }

        if (isset($dbConfig['dump'])) {
            $dbDumper = static::processExtraDumpParameters($dbConfig['dump'], $dbDumper);
        }

        if (isset($dbConfig['unix_socket'])) {
            $dbDumper = $dbDumper->setSocket($dbConfig['unix_socket']);
        }

        return $dbDumper;
    }

    public static function extend(string $driver, callable $callback): void
    {
        static::$custom[$driver] = $callback;
    }

    protected static function forDriver(string $dbDriver): DbDumper
    {
        $driver = strtolower($dbDriver);

        if (isset(static::$custom[$driver])) {
            return (static::$custom[$driver])();
        }

        return match ($driver) {
            'mysql' => new MySql,
            'mariadb' => new MariaDb,
            'pgsql' => new PostgreSql,
            'sqlite' => new Sqlite,
            'mongodb' => new MongoDb,
            default => throw CannotCreateDbDumper::unsupportedDriver($driver),
        };
    }

    /** @param array<string, string|array<string>> $dumpConfiguration */
    protected static function processExtraDumpParameters(array $dumpConfiguration, DbDumper $dbDumper): DbDumper
    {
        collect($dumpConfiguration)->each(function ($configValue, $configName) use ($dbDumper) {
            $methodName = lcfirst(Str::studly(is_numeric($configName) ? $configValue : $configName));
            $methodValue = is_numeric($configName) ? null : $configValue;

            $methodName = static::determineValidMethodName($dbDumper, $methodName);

            if (method_exists($dbDumper, $methodName)) {
                static::callMethodOnDumper($dbDumper, $methodName, $methodValue);
            }
        });

        return $dbDumper;
    }

    protected static function callMethodOnDumper(DbDumper $dbDumper, string $methodName, mixed $methodValue): DbDumper
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
            ->first(fn (string $methodName) => method_exists($dbDumper, $methodName), '');
    }
}
