<?php

namespace Spatie\Backup\Tests;

use ZipArchive;
use Spatie\Backup\Tasks\Backup\Zip;

class ZipTest extends TestCase
{
    /** @var string */
    protected $pathToZip;

    /** @var \Spatie\Backup\Tasks\Backup\Zip */
    protected $zip;

    public function setUp(): void
    {
        parent::setUp();

        $this->initializeTempDirectory();

        $this->pathToZip = "{$this->getTempDirectory()}/test.zip";

        $this->zip = new Zip($this->pathToZip);
    }

    /** @test */
    public function it_can_create_a_zip_file()
    {
        $this->zip->add(__FILE__);
        $this->zip->close();

        $this->assertFileExists($this->pathToZip);
    }

    /** @test */
    public function it_can_report_its_own_size()
    {
        $this->assertEquals(0, $this->zip->size());

        $this->zip->add(__FILE__);
        $this->zip->close();

        $this->assertNotEquals(0, $this->zip->size());
    }

    /** @test */
    public function it_can_password_protect_a_zip_file()
    {
        $this->zip->setPassword('password');

        $this->zip->add(__FILE__);

        $this->zip->close();

        $zipToTest = new ZipArchive($this->pathToZip);
        $zipFilename = Zip::formatZipFilename(__FILE__);

        $this->assertTrue($zipToTest->open($this->pathToZip));

        if (method_exists(ZipArchive::class, 'setEncryptionName')) {
            // Check if we cannot read the file because no password is given
            $this->assertFalse($zipToTest->getFromName($zipFilename));
            $this->assertEquals('No password provided', $zipToTest->getStatusString());

            // Check if we cannot read the file because a invalid password is given
            $zipToTest->setPassword('invalid password');
            $this->assertFalse($zipToTest->getFromName($zipFilename));
            $this->assertEquals('Wrong password provided', $zipToTest->getStatusString());

            // Check if we cannot read the file because a invalid password is given
            $zipToTest->setPassword('password');
            $this->assertNotEmpty($zipToTest->getFromName($zipFilename));
        } else {
            $this->assertNotEmpty($zipToTest->getFromName($zipFilename));
        }
    }
}
