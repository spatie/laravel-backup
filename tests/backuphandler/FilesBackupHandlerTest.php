<?php

use Spatie\Backup\BackupHandlers\Files\FilesBackupHandler;

class FilesBackupHandlerTest extends Orchestra\Testbench\TestCase {

    protected $backupHandler;

    public function setUp()
    {
        parent::setUp();
        $this->backupHandler = new FilesBackupHandler();
    }


    public function test_if_correct_files_are_returned_using_fileInclude_noExclude()
    {
        $this->backupHandler->setIncludedFiles([realpath('tests/_data/CelineDion.php')]);
        $this->backupHandler->setExcludedFiles([]);

        $files = $this->backupHandler->getFilesToBeBackedUp();

        $this->assertEquals(realpath('tests/_data/CelineDion.php'), $files[0]);
    }

    public function test_if_correct_files_are_returned_using_fileInclude_fileExclude()
    {
        $this->backupHandler->setIncludedFiles([realpath('tests/_data/BackstreetBoys/Nineties.php'), realpath('tests/_data/BackstreetBoys/WelcomeToThe.php')]);
        $this->backupHandler->setExcludedFiles([realpath('tests/_data/BackstreetBoys/Nineties.php')]);

        $files = $this->backupHandler->getFilesToBeBackedUp();

        $this->assertEquals(1, count($files));
        $this->assertNotEquals(realpath('tests/_data/Nineties.php'), $files[1]);
    }

    public function test_if_correct_files_are_returned_using_fileInclude_directoryExclude()
    {
        $this->backupHandler->setIncludedFiles([realpath('tests/_data/JustinBieber/SelenaGomez.php')]);
        $this->backupHandler->setExcludedFiles([realpath('tests/_data/JustinBieber')]);

        $files = $this->backupHandler->getFilesToBeBackedUp();

        $this->assertEmpty($files);

    }

    public function test_if_correct_files_are_returned_using_directoryInclude_noExclude()
    {
        $this->backupHandler->setIncludedFiles([realpath('tests/_data/JustinBieber')]);
        $this->backupHandler->setExcludedFiles([]);

        $files = $this->backupHandler->getFilesToBeBackedUp();

        $this->assertArrayHasKey(realpath('tests/_data/JustinBieber/SelenaGomez.php'), $files);
        $this->assertArrayHasKey(realpath('tests/_data/JustinBieber/Paparazzi.php'), $files);
    }

    public function test_if_correct_files_are_returned_using_directoryInclude_fileExclude()
    {
        $this->backupHandler->setIncludedFiles([realpath('tests/_data/OneDirection')]);
        $this->backupHandler->setExcludedFiles([realpath('tests/_data/OneDirection/Harry.php')]);

        $files = $this->backupHandler->getFilesToBeBackedUp();

        $this->assertArrayNotHasKey(realpath('tests/_data/OneDirection/harry.php'), $files);

        $this->assertArrayHasKey(realpath('tests/_data/OneDirection/SimonCowell.php'), $files);
        $this->assertArrayHasKey(realpath('tests/_data/OneDirection/GenericBoysBandDude.php'), $files);
    }

    public function test_if_correct_files_are_returned_using_directoryInclude_directoryExclude()
    {
        $this->backupHandler->setIncludedFiles([realpath('tests/_data/')]);
        $this->backupHandler->setExcludedFiles([realpath('tests/_data/JustinBieber')]);

        $files = $this->backupHandler->getFilesToBeBackedUp();

        $this->assertArrayNotHasKey(realpath('tests/_data/JustinBieber/SelenaGomez.php'), $files);
        $this->assertArrayNotHasKey(realpath('tests/_data/JustinBieber/Paparazzi.php'), $files);

        $this->assertArrayHasKey(realpath('tests/_data/BackstreetBoys/WelcomeToThe.php'), $files);
        $this->assertArrayHasKey(realpath('tests/_data/BackstreetBoys/Nineties.php'), $files);

        $this->assertArrayHasKey(realpath('tests/_data/OneDirection/Harry.php'), $files);

        $this->assertArrayHasKey(realpath('tests/_data/TakeThat/RobbieWilliams.php'), $files);
    }
}
