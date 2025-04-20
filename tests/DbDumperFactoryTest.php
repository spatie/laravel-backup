<?php

use Spatie\Backup\Exceptions\CannotCreateDbDumper;
use Spatie\Backup\Tasks\Backup\DbDumperFactory;
use Spatie\DbDumper\Databases\MariaDb;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;

beforeEach(function () {
    config()->set('database.default', 'mysql');

    config()->set('database.connections.mariadb', [
        'driver' => 'mariadb',
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'myDb',
        'username' => 'root',
        'password' => 'myPassword',
    ]);

    config()->set('database.connections.mongodb', [
        'driver' => 'mongodb',
        'host' => 'localhost',
        'port' => 27017,
        'database' => 'myDb',
        'username' => 'root',
        'password' => 'myPassword',
        'options' => [
            'database' => 'admin',
        ],
        'dump' => [
            'mongodb_user_auth' => 'admin',
        ],
    ]);

    config()->set('database.connections.pgsql', [
        'driver' => 'pgsql',
        'url' => 'pgsql://homestead:password:15432@localhost/homestead',
        'host' => '127.0.0.1',
        'port' => '5432',
        'database' => 'forge',
        'username' => 'forge',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
    ]);
});

it('can create instances of mysql and mariadb and pgsql and mongodb', function () {
    expect(DbDumperFactory::createFromConnection('mysql'))->toBeInstanceOf(MySql::class);
    expect(DbDumperFactory::createFromConnection('mariadb'))->toBeInstanceOf(MariaDb::class);
    expect(DbDumperFactory::createFromConnection('pgsql'))->toBeInstanceOf(PostgreSql::class);
    expect(DbDumperFactory::createFromConnection('mongodb'))->toBeInstanceOf(MongoDb::class);
});

it('can create sqlite instance', function () {
    config()->set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => 'database.sqlite',
    ]);

    expect(DbDumperFactory::createFromConnection('sqlite'))->toBeInstanceOf(Sqlite::class);
});

it('can create mongodb instance', function () {
    $dbConfig = [
        'driver' => 'mongodb',
        'host' => 'localhost',
        'port' => 27017,
        'database' => 'myDb',
        'username' => 'root',
        'password' => 'myPassword',
        'options' => [
            'database' => 'admin', // sets the authentication database required by mongo 3
        ],
    ];
    config()->set('database.connections.mongodb', $dbConfig);
    expect(DbDumperFactory::createFromConnection('mongodb'))->toBeInstanceOf(MongoDb::class);
});

it('can create instance from database url', function () {
    $dbConfig = [
        'driver' => 'pgsql',
        'host' => 'localhost',
        'port' => '15432',
        'database' => 'forge',
        'username' => 'homestead',
        'password' => 'password',
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
    ];
    config()->set('database.connections.pgsql', $dbConfig);
    expect(DbDumperFactory::createFromConnection('pgsql'))->toBeInstanceOf(PostgreSql::class);
});

it('ignores malformed ports', function () {
    $dbConfig = [
        'driver' => 'mysql',
        'read' => [
            'host' => 'localhost-read',
            'database' => 'myDb-read',
            'port' => ''
        ],
        'write' => [
            'host' => 'localhost-write',
            'database' => 'myDb-write',
            'port' => 'fish'
        ],
        'username' => 'root',
        'password' => 'myPassword',
        'dump' => ['add_extra_option' => '--extra-option=value'],
    ];

    config()->set('database.connections.mysql', $dbConfig);

    $dumper = DbDumperFactory::createFromConnection('mysql');

    expect($dumper->getPort())->toEqual(null);
});

it('will use the read db when one is defined', function () {
    $dbConfig = [
        'driver' => 'mysql',
        'read' => [
            'host' => 'localhost-read',
            'database' => 'myDb-read',
        ],
        'write' => [
            'host' => 'localhost-write',
            'database' => 'myDb-write',
        ],
        'username' => 'root',
        'password' => 'myPassword',
        'dump' => ['add_extra_option' => '--extra-option=value'],
    ];

    config()->set('database.connections.mysql', $dbConfig);

    $dumper = DbDumperFactory::createFromConnection('mysql');

    expect($dumper->getHost())->toEqual('localhost-read');
    expect($dumper->getDbName())->toEqual('myDb-read');
});

it('will use the first read db when multiple are defined', function () {
    $dbConfig = [
        'driver' => 'mysql',
        'read' => [
            'host' => ['localhost-read-1', 'localhost-read-2'],
            'database' => 'myDb-read',
        ],
        'write' => [
            'host' => 'localhost-write',
            'database' => 'myDb-write',
        ],
        'username' => 'root',
        'password' => 'myPassword',
        'dump' => ['add_extra_option' => '--extra-option=value'],
    ];

    config()->set('database.connections.mysql', $dbConfig);

    $dumper = DbDumperFactory::createFromConnection('mysql');

    expect($dumper->getHost())->toEqual('localhost-read-1');
    expect($dumper->getDbName())->toEqual('myDb-read');
});

it('will throw an exception when creating an unknown type of dumper', function () {
    $this->expectException(CannotCreateDbDumper::class);

    DbDumperFactory::createFromConnection('unknown type');
});

it('can add named options to the dump command', function () {
    $dumpConfig = ['use_single_transaction'];

    config()->set('database.connections.mysql.dump', $dumpConfig);

    expect(getDumpCommand())->toContain('--single-transaction');
});

it('can add named options with an array value to the dump command', function () {
    $dumpConfig = ['include_tables' => ['table1', 'table2']];

    config()->set('database.connections.mysql.dump', $dumpConfig);

    expect(getDumpCommand())->toContain(implode(' ', $dumpConfig['include_tables']));
});

it('can add arbritrary options to the dump command', function () {
    $dumpConfig = ['add_extra_option' => '--extra-option=value'];

    config()->set('database.connections.mysql.dump', $dumpConfig);

    expect(getDumpCommand())->toContain($dumpConfig['add_extra_option']);
});

it('can create instances of custom dumpers', function () {
    DbDumperFactory::extend('mysql', function () {
        return new MongoDb;
    });

    expect(DbDumperFactory::createFromConnection('mysql'))->toBeInstanceOf(MongoDb::class);
});

function getDumpCommand(): string
{
    $dumpFile = '';
    $credentialsFile = '';

    return DbDumperFactory::createFromConnection('mysql')->getDumpCommand($dumpFile, $credentialsFile);
}
