<?php

namespace Spatie\Backup\Test\Unit;

use Spatie\Backup\Test\TestHelper;
use Spatie\Backup\Tasks\Backup\Gzip;

class GzipTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_gzip_a_test_file()
    {
        $gzip = new Gzip((new TestHelper())->getStubDirectory().'/file1.txt');

        // check for gzip identifier
        $this->assertSame(0, mb_strpos(file_get_contents($gzip->filePath), "\x1f"."\x8b"."\x08"));

        unlink($gzip->filePath);
    }
}
