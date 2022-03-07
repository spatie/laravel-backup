<?php

use Spatie\Backup\Exceptions\CannotCreateDbDumper;
use Spatie\Backup\Tasks\Backup\DbDumperFactory;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;

uses(TestCase::class);

beforeEach(function () {
    config()->set('database.default', 'mysql');

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

it('can create instances of mysql and pgsql and mongodb', function () {
    $this->assertInstanceOf(MySql::class, DbDumperFactory::createFromConnection('mysql'));
    $this->assertInstanceOf(PostgreSql::class, DbDumperFactory::createFromConnection('pgsql'));
    $this->assertInstanceOf(MongoDb::class, DbDumperFactory::createFromConnection('mongodb'));
});

it('can create sqlite instance', function () {
    config()->set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => 'database.sqlite',
    ]);

    $this->assertInstanceOf(Sqlite::class, DbDumperFactory::createFromConnection('sqlite'));
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
    $this->assertInstanceOf(MongoDb::class, DbDumperFactory::createFromConnection('mongodb'));
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
    $this->assertInstanceOf(PostgreSql::class, DbDumperFactory::createFromConnection('pgsql'));
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

    $this->assertEquals('localhost-read', $dumper->getHost());
    $this->assertEquals('myDb-read', $dumper->getDbName());
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

    $this->assertEquals('localhost-read-1', $dumper->getHost());
    $this->assertEquals('myDb-read', $dumper->getDbName());
});

it('will throw an exception when creating an unknown type of dumper', function () {
    $this->expectException(CannotCreateDbDumper::class);

    DbDumperFactory::createFromConnection('unknown type');
});

it('can add named options to the dump command', function () {
    $dumpConfig = ['use_single_transaction'];

    config()->set('database.connections.mysql.dump', $dumpConfig);

    $this->assertStringContainsString('--single-transaction', getDumpCommand());
});

it('can add named options with an array value to the dump command', function () {
    $dumpConfig = ['include_tables' => ['table1', 'table2']];

    config()->set('database.connections.mysql.dump', $dumpConfig);

    $this->assertStringContainsString(implode(' ', $dumpConfig['include_tables']), getDumpCommand());
});

it('can add arbritrary options to the dump command', function () {
    $dumpConfig = ['add_extra_option' => '--extra-option=value'];

    config()->set('database.connections.mysql.dump', $dumpConfig);

    $this->assertStringContainsString($dumpConfig['add_extra_option'], getDumpCommand());
});

it('can create instances of custom dumpers', function () {
    DbDumperFactory::extend('mysql', function () {
        return new MongoDb();
    });

    $this->assertInstanceOf(MongoDb::class, DbDumperFactory::createFromConnection('mysql'));
});

// Helpers
function getDumpCommand(): string
{
    $dumpFile = '';
    $credentialsFile = '';

    return DbDumperFactory::createFromConnection('mysql')->getDumpCommand($dumpFile, $credentialsFile);
}
