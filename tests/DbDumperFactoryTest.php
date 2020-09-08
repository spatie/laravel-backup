<?php

namespace Spatie\Backup\Tests;

use Spatie\Backup\Exceptions\CannotCreateDbDumper;
use Spatie\Backup\Tasks\Backup\DbDumperFactory;
use Spatie\DbDumper\Databases\MongoDb;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;

class DbDumperFactoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

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
    }

    /** @test */
    public function it_can_create_instances_of_mysql_and_pgsql_and_mongodb()
    {
        $this->assertInstanceOf(MySql::class, DbDumperFactory::createFromConnection('mysql'));
        $this->assertInstanceOf(PostgreSql::class, DbDumperFactory::createFromConnection('pgsql'));
        $this->assertInstanceOf(MongoDb::class, DbDumperFactory::createFromConnection('mongodb'));
    }

    /** @test */
    public function it_can_create_sqlite_instance()
    {
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => 'database.sqlite',
        ]);

        $this->assertInstanceOf(Sqlite::class, DbDumperFactory::createFromConnection('sqlite'));
    }

    /** @test */
    public function it_can_create_mongodb_instance()
    {
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
    }

    /** @test */
    public function it_can_create_instance_from_database_url()
    {
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
    }

    /** @test */
    public function it_will_use_the_read_db_when_one_is_defined()
    {
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
    }

    /** @test */
    public function it_will_use_the_first_read_db_when_multiple_are_defined()
    {
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
    }

    /** @test */
    public function it_will_throw_an_exception_when_creating_an_unknown_type_of_dumper()
    {
        $this->expectException(CannotCreateDbDumper::class);

        DbDumperFactory::createFromConnection('unknown type');
    }

    /** @test */
    public function it_can_add_named_options_to_the_dump_command()
    {
        $dumpConfig = ['use_single_transaction'];

        config()->set('database.connections.mysql.dump', $dumpConfig);

        $this->assertStringContainsString('--single-transaction', $this->getDumpCommand());
    }

    /** @test */
    public function it_can_add_named_options_with_an_array_value_to_the_dump_command()
    {
        $dumpConfig = ['include_tables' => ['table1', 'table2']];

        config()->set('database.connections.mysql.dump', $dumpConfig);

        $this->assertStringContainsString(implode(' ', $dumpConfig['include_tables']), $this->getDumpCommand());
    }

    /** @test */
    public function it_can_add_arbritrary_options_to_the_dump_command()
    {
        $dumpConfig = ['add_extra_option' => '--extra-option=value'];

        config()->set('database.connections.mysql.dump', $dumpConfig);

        $this->assertStringContainsString($dumpConfig['add_extra_option'], $this->getDumpCommand());
    }

    /** @test */
    public function it_can_create_instances_of_custom_dumpers()
    {
        DbDumperFactory::extend('mysql', function () {
            return new MongoDb();
        });

        $this->assertInstanceOf(MongoDb::class, DbDumperFactory::createFromConnection('mysql'));
    }

    protected function getDumpCommand(): string
    {
        $dumpFile = '';
        $credentialsFile = '';

        return DbDumperFactory::createFromConnection('mysql')->getDumpCommand($dumpFile, $credentialsFile);
    }
}
