<?php

namespace Spatie\Backup\Test\Unit;

use Spatie\Backup\Test\TestHelper;
use Spatie\Backup\Tasks\Backup\Zip;
use Spatie\Backup\Test\Integration\TestCase;

class ZipTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

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

    /** @test */
    public function it_can_say_that_a_file_is_inside_a_zip_file()
    {
        $pathToZip = "{$this->testHelper->getTempDirectory()}/test.zip";

        $zip = new Zip($pathToZip);

        $zip->add(__FILE__);

        $fileName = __FILE__;

        $this->assertFileExistsInZipFile( $fileName , $pathToZip );

    }

    /** @test */
    public function it_can_say_that_a_file_is_not_inside_a_zip_file()
    {
        $pathToZip = "{$this->testHelper->getTempDirectory()}/test.zip";

        $zip = new Zip($pathToZip);

        $fileName = __FILE__;

        $zip->add($fileName);
        
        #change the file name. 
        $fileName .="x";

        $this->assertFileDoesNotExistsInZipFile( $fileName , $pathToZip );

    }

    /** @test */
    public function it_can_get_a_file_from_a_zip_file()
    {
        $pathToZip = "{$this->testHelper->getTempDirectory()}/test.zip";

        $zip = new Zip($pathToZip);

        $fileName = __FILE__;

        $zip->add($fileName);
        
        $file = $zip->getFile( $fileName );

        $isStream = $file !== false;

        $this->assertEquals( true, $isStream );

    }
}
