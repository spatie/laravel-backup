<?php

namespace Spatie\Backup\Test\Unit;

use Spatie\Backup\Exceptions\CannotCreateDbDumper;
use Spatie\Backup\Tasks\Backup\DbDumperFactory;
use Spatie\Backup\Test\Integration\TestCase;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;

class DbDumperFactoryTest extends TestCase
{
    /** @test */
    public function it_can_create_instances_of_mysql_and_pgsql()
    {
        $this->assertInstanceOf(MySql::class, DbDumperFactory::create('mysql'));
        $this->assertInstanceOf(PostgreSql::class, DbDumperFactory::create('pgsql'));
    }

    /** @test */
    public function it_will_throw_an_exception_when_creating_an_unknown_type_of_dumper()
    {
        $this->expectException(CannotCreateDbDumper::class);

        DbDumperFactory::create('unknown type');
    }
}
