<?php

namespace Spatie\Backup\Test\Unit;

use PHPUnit_Framework_TestCase;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\DbDumperFactory;
use Spatie\DbDumper\Exceptions\CannotCreateDumper;

class DbDumperFactoryTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_create_instances_of_mysql_and_pgsql()
    {
        $this->assertInstanceOf(MySql::class, DbDumperFactory::create('mysql'));
        $this->assertInstanceOf(PostgreSql::class, DbDumperFactory::create('pgsql'));
    }

    /** @test */
    public function it_can_use_case_insensitive_type_names()
    {
        $this->assertInstanceOf(MySql::class, DbDumperFactory::create('MySQL'));
        $this->assertInstanceOf(PostgreSql::class, DbDumperFactory::create('PgSql'));
    }

    /** @test */
    public function it_will_throw_an_exception_when_creating_an_unknown_type_of_dumper()
    {
        $this->expectException(CannotCreateDumper::class);

        DbDumperFactory::create('unknown type');
    }
}
