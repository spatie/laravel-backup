<?php

namespace Spatie\Backup\Test\Integration;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Events\BackupHasFailed;

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

        $this->app['config']->set('laravel-backup.backup.source.files.include', [base_path()]);
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
    public function it_includes_files_from_the_local_disks_in_the_backup()
    {
        $backupDisk = $this->app['config']->get('filesystems.disks.local.root');

        $this->app['config']->set('laravel-backup.backup.source.files.include', [$backupDisk]);

        touch($backupDisk.DIRECTORY_SEPARATOR.'testing-file.txt');

        Artisan::call('backup:run', ['--only-files' => true]);

        $zipFullPath = $backupDisk.DIRECTORY_SEPARATOR.$this->expectedZipPath;
        $this->assertFileExistsInZip($zipFullPath, 'testing-file.txt');
    }

    /** @test */
    public function it_excludes_the_backup_destination_from_the_backup()
    {
        $backupDisk = $this->app['config']->get('filesystems.disks.local.root');

        $this->app['config']->set('laravel-backup.backup.source.files.include', [$backupDisk]);

        mkdir($backupDisk.DIRECTORY_SEPARATOR.'mysite.com', 0777, true);
        touch($backupDisk.DIRECTORY_SEPARATOR.'mysite.com'.DIRECTORY_SEPARATOR.'testing-file.txt');

        Artisan::call('backup:run', ['--only-files' => true]);

        $zipFullPath = $backupDisk.DIRECTORY_SEPARATOR.$this->expectedZipPath;
        $this->assertFileDoesntExistsInZip($zipFullPath, 'testing-file.txt');
    }

    /** @test */
    public function it_excludes_the_temporary_directory_from_the_backup()
    {
        $backupDisk = $this->app['config']->get('filesystems.disks.local.root');

        $tempDirectoryPath = storage_path('app/laravel-backup/temp');

        if (! file_exists($tempDirectoryPath)) {
            mkdir($tempDirectoryPath, 0777, true);
        }
        touch($tempDirectoryPath.DIRECTORY_SEPARATOR.'testing-file.txt');

        Artisan::call('backup:run', ['--only-files' => true]);

        $zipFullPath = $backupDisk.DIRECTORY_SEPARATOR.$this->expectedZipPath;
        $this->assertFileDoesntExistsInZip($zipFullPath, 'testing-file.txt');
    }

    /** @test */
    public function it_can_backup_using_a_custom_filename_as_option()
    {
        $this->date = Carbon::create('2016', 1, 1, 9, 1, 1);

        Carbon::setTestNow($this->date);

        $filename = 'testing-filename.zip';

        $this->expectedZipPath = 'mysite.com/'.$filename;

        $resultCode = Artisan::call('backup:run', ['--only-files' => true, '--filename' => $filename]);

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

    /** @test */
    public function it_appends_the_database_type_to_backup_file_name_to_prevent_overwrite()
    {
        $this->app['config']->set('laravel-backup.backup.source.databases', ['sqlite']);

        $this->setUpDatabase($this->app);

        $resultCode = Artisan::call('backup:run', ['--only-db' => true]);

        $this->assertEquals(0, $resultCode);

        $backupDiskLocal = $this->app['config']->get('filesystems.disks.local.root');
        $backupFileLocal = $backupDiskLocal.DIRECTORY_SEPARATOR.$this->expectedZipPath;
        $this->assertFileExistsInZip($backupFileLocal, 'sqlite-database.sql');

        $backupDiskSecondLocal = $this->app['config']->get('filesystems.disks.secondLocal.root');
        $backupFileSecondLocal = $backupDiskSecondLocal.DIRECTORY_SEPARATOR.$this->expectedZipPath;
        $this->assertFileExistsInZip($backupFileSecondLocal, 'sqlite-database.sql');

        /*
         * Close the database connection to unlock the sqlite file for deletion.
         * This prevents the errors from other tests trying to delete and recreate the folder.
         */
        $this->app['db']->disconnect();
    }

    /** @test */
    public function it_should_start_with_a_clean_temp_directory()
    {
        // start with a file in the backup temp directory
        $tempDirectoryPath = storage_path('app/laravel-backup/temp');
        if (! file_exists($tempDirectoryPath)) {
            mkdir($tempDirectoryPath, 0777, true);
        }
        touch($tempDirectoryPath.DIRECTORY_SEPARATOR.'testing-file.txt');

        // we expect the backup to fail
        $this->expectsEvent(BackupHasFailed::class);
        $this->app['config']->set('laravel-backup.backup.source.files.include', []);
        $this->app['config']->set('laravel-backup.backup.source.databases', []);
        Artisan::call('backup:run');

        // make sure the file we added to temp is now gone
        $this->assertFalse(realpath($tempDirectoryPath.DIRECTORY_SEPARATOR.'testing-file.txt'));
    }

    /** @test */
    public function it_should_trigger_the_backup_failed_event()
    {
        $this->expectsEvent(BackupHasFailed::class);

        //since our test environment did not set up a db, this will fail
        Artisan::call('backup:run', [
            '--only-db' => true,
        ]);
    }

    /** @test */
    public function it_should_omit_the_backup_failed_event_with_no_notifications_flag()
    {
        $this->doesNotExpectEvent(BackupHasFailed::class);

        //since our test environment did not set up a db, this will fail
        Artisan::call('backup:run', [
            '--only-db' => true,
            '--disable-notifications' => true,
        ]);
    }
}
