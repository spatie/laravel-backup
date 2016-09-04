<?php

namespace Spatie\Backup\Test\Unit;

use Spatie\Backup\Tasks\Backup\Zip;
use Spatie\Backup\Test\TestHelper;

class ZipTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Spatie\Backup\Test\TestHelper */
    protected $testHelper;

    public function setUp()
    {
        parent::setUp();

        $this->testHelper = new TestHelper();

        $this->testHelper->initializeTempDirectory();
    }

    /** @test */
    public function it_can_create_a_zip_file()
    {
        $pathToZip = "{$this->testHelper->getTempDirectory()}/test.zip";

        $zip = new Zip($pathToZip);

        $zip->add(__FILE__);

        $this->assertFileExists($pathToZip);
    }
}
