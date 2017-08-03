<?php

namespace Spatie\Backup\Test\Unit;

use Exception;
use Spatie\Backup\Test\TestHelper;
use Spatie\Backup\Tasks\Backup\Gzip;
use PHPUnit\Framework\TestCase;

class GzipTest extends TestCase
{
    /** @test */
    public function it_can_gzip_a_file()
    {
        $compressedFile = Gzip::compress((new TestHelper())->getStubDirectory().'/file1.txt');

        // check for gzip identifier
        $this->assertSame(0, mb_strpos(file_get_contents($compressedFile), "\x1f"."\x8b"."\x08"));

        unlink($compressedFile);
    }

    /** @test */
    public function it_will_throw_an_exception_if_the_given_file_does_not_exist()
    {
        $this->expectException(Exception::class);

        Gzip::compress('nonexistent.txt');
    }
}
