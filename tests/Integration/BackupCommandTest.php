<?php

namespace Spatie\Backup\Test\Integration;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class BackupCommandTest extends TestCase
{
    /** @var \Carbon\Carbon */
    protected $date;

    /** @var string */
    protected $expectedZipPath;

    public function setUp()
    {
        parent::setUp();

        $this->date = Carbon::create('2016', 1, 1, 21, 1, 1);

        Carbon::setTestNow($this->date);

        $this->expectedZipPath = 'mysite.com/2016-01-01-21-01-01.zip';

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

        $this->assertFileExistsOnDisk($this->expectedZipPath, 'local');

        $this->assertFileExistsOnDisk($this->expectedZipPath, 'secondLocal');
    }

    /** @test */
    public function it_can_backup_using_a_custom_filename()
    {
        $this->date = Carbon::create('2016', 1, 1, 9, 1, 1);

        Carbon::setTestNow($this->date);

        $this->app['config']->set('laravel-backup.backup.destination.filename_prefix', 'custom_name_');

        $this->expectedZipPath = 'mysite.com/custom_name_2016-01-01-09-01-01.zip';

        $resultCode = Artisan::call('backup:run', ['--only-files' => true]);

        $this->assertEquals(0, $resultCode);

        $this->assertFileExistsOnDisk($this->expectedZipPath, 'local');

        $this->assertFileExistsOnDisk($this->expectedZipPath, 'secondLocal');
    }

    /** @test */
    public function it_can_backup_to_a_specific_disk()
    {
        $resultCode = Artisan::call('backup:run', [
            '--only-files'   => true,
            '--only-to-disk' => 'secondLocal',
        ]);

        $this->assertEquals(0, $resultCode);

        $this->assertFileNotExistsOnDisk($this->expectedZipPath, 'local');
        $this->assertFileExistsOnDisk($this->expectedZipPath, 'secondLocal');
    }

    /** @test */
    public function it_can_backup_twice_a_day_at_same_time_in_12h_clock()
    {
        // first backup
        $this->date = Carbon::create('2016', 1, 1, 9, 1, 1);

        Carbon::setTestNow($this->date);

        $this->expectedZipPath = 'mysite.com/2016-01-01-09-01-01.zip';

        $resultCode = Artisan::call('backup:run', ['--only-files' => true]);

        $this->assertEquals(0, $resultCode);

        $this->assertFileExistsOnDisk($this->expectedZipPath, 'local');

        $this->assertFileExistsOnDisk($this->expectedZipPath, 'secondLocal');

        // second backup
        $this->date = Carbon::create('2016', 1, 1, 21, 1, 1);

        Carbon::setTestNow($this->date);

        $this->expectedZipPath = 'mysite.com/2016-01-01-21-01-01.zip';

        $resultCode = Artisan::call('backup:run', ['--only-files' => true]);

        $this->assertEquals(0, $resultCode);

        $this->assertFileExistsOnDisk($this->expectedZipPath, 'local');

        $this->assertFileExistsOnDisk($this->expectedZipPath, 'secondLocal');
    }

    /** @test */
    public function it_will_fail_when_try_to_backup_only_the_files_and_only_the_db()
    {
        $resultCode = Artisan::call('backup:run', [
            '--only-files' => true,
            '--only-db'    => true,
        ]);

        $this->assertEquals(-1, $resultCode);

        $this->seeInConsoleOutput('Cannot use `only-db` and `only-files` together.');

        $this->assertFileNotExistsOnDisk($this->expectedZipPath, 'local');
        $this->assertFileNotExistsOnDisk($this->expectedZipPath, 'secondLocal');
    }

    /** @test */
    public function it_will_fail_when_trying_to_backup_a_non_existing_database()
    {
        //since our test environment did not set up a db, this will fail
        Artisan::call('backup:run', [
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

        $this->assertFileNotExistsOnDisk($this->expectedZipPath, 'local');
        $this->assertFileNotExistsOnDisk($this->expectedZipPath, 'secondLocal');
    }

    /** @test */
    public function it_will_fail_when_there_are_no_files_to_be_backed_up()
    {
        $this->app['config']->set('laravel-backup.backup.source.files.include', []);
        $this->app['config']->set('laravel-backup.backup.source.databases', []);

        Artisan::call('backup:run');

        $this->seeInConsoleOutput('There are no files to be backed up');
    }
}
