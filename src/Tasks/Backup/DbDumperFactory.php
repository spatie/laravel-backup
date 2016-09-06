<?php

namespace Spatie\Backup\Tasks\Backup;

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

        $dbDumper = self::create($dbConfig['driver'])
            ->setHost($dbConfig['host'])
            ->setDbName($dbConfig['database'])
            ->setUserName($dbConfig['username'])
            ->setPassword($dbConfig['password'])
            ->setDumpBinaryPath($dbConfig['dump_command_path'] ?? '')
            ->setTimeout(isset($dbConfig['dump_command_timeout']) ?? 0);

        if (isset($dbConfig['dump'])) {
            $dbDumper = static::processExtraDumpParameters($dbConfig['dump'], $dbDumper);
        }

        return $dbDumper;
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
        collect($dumpConfiguration)->filter(function (string $configValue, string $configName) use ($dbDumper) {
            return method_exists($dbDumper, self::getDumperMethodName($configName));
        })->each(function (string $configValue, string $configName) use ($dbDumper) {
            $methodName = self::getDumperMethodName($configName);

            $dbDumper->$methodName($configValue);
        });

        return $dbDumper;
    }

    protected function getDumperMethodName(string $configName): string
    {
        return 'set'.studly_case($configName);
    }
}
