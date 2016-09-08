<?php

namespace Spatie\Backup\Test\Integration;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\BackupHasFailed;

class BackupCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('laravel-backup.backup.destination.disks', [
            'local',
            'secondLocal',
        ]);
    }

    /** @test */
    public function it_can_backup_only_the_files()
    {
        $resultCode = Artisan::call('backup:run', ['--only-files' => true]);

        $this->assertEquals(0, $resultCode);

        $this->assertFileWithExtensionExistInDirectoryOnDisk('zip', 'mysite.com', 'local');
        $this->assertFileWithExtensionExistInDirectoryOnDisk('zip', 'mysite.com', 'secondLocal');
    }

    /** @test */
    public function it_can_backup_to_a_specific_disk()
    {
        $resultCode = Artisan::call('backup:run', [
            '--only-files' => true,
            '--only-to-disk' => 'secondLocal',
        ]);

        $this->assertEquals(0, $resultCode);

        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'local');
        $this->assertFileWithExtensionExistInDirectoryOnDisk('zip', 'mysite.com', 'secondLocal');
    }

    /** @test */
    public function it_will_fail_when_try_to_backup_only_the_files_and_only_the_db()
    {
        $resultCode = Artisan::call('backup:run', [
            '--only-files' => true,
            '--only-db' => true,
        ]);

        $this->assertEquals(-1, $resultCode);

        $this->seeInConsoleOutput('Cannot use only-db and only-files together');

        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'local');
        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'secondLocal');
    }

    /** @test */
    public function it_will_fail_when_trying_to_backup_a_non_existing_database()
    {
        //since our test environment did not set up a db, this will fail
        $resultCode = Artisan::call('backup:run', [
            '--only-db' => true,
        ]);

        $this->seeInConsoleOutput('Backup failed');
    }

    /** @test */
    public function it_will_fail_when_trying_to_backup_to_an_non_existing_diskname()
    {
        $resultCode = Artisan::call('backup:run', [
            '--only-to-disk' => 'non existing disk',
        ]);

        $this->assertEquals(-1, $resultCode);

        $this->seeInConsoleOutput('There is not backup destination with a disk named');

        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'local');
        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'secondLocal');
    }

    /** @test */
    public function it_will_fail_when_there_are_no_file_to_be_backed_up()
    {
        $this->app['config']->set('laravel-backup.backup.source.files.include', []);
        $this->app['config']->set('laravel-backup.backup.source.databases', []);

        Artisan::call('backup:run');

        $this->seeInConsoleOutput("There are no files to be backed up");
    }
}
