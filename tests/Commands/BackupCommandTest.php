<?php

namespace Spatie\Backup\Tests\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Tests\TestCase;
use Spatie\DbDumper\Compressors\GzipCompressor;

class BackupCommandTest extends TestCase
{
    /** @var \Carbon\Carbon */
    protected $date;

    /** @var string */
    protected $expectedZipPath;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->setNow(2016, 1, 1, 21, 1, 1);

        $this->expectedZipPath = 'mysite/2016-01-01-21-01-01.zip';

        config()->set('backup.backup.destination.disks', [
            'local',
            'secondLocal',
        ]);

        config()->set('backup.backup.source.files.include', [base_path()]);

        config()->set('backup.backup.source.databases', [
            'db1',
            'db2',
        ]);
    }

    /** @test */
    public function it_can_backup_only_the_files()
    {
        $this->artisan('backup:run --only-files')->assertExitCode(0);

        Storage::disk('local')->assertExists($this->expectedZipPath);
        Storage::disk('secondLocal')->assertExists($this->expectedZipPath);
    }

    /** @test */
    public function it_can_backup_using_a_custom_filename()
    {
        $this->date = Carbon::create('2016', 1, 1, 9, 1, 1);

        Carbon::setTestNow($this->date);

        config()->set('backup.backup.destination.filename_prefix', 'custom_name_');

        $this->expectedZipPath = 'mysite/custom_name_2016-01-01-09-01-01.zip';

        $this->artisan('backup:run', ['--only-files' => true])->assertExitCode(0);

        Storage::disk('local')->assertExists($this->expectedZipPath);
        Storage::disk('secondLocal')->assertExists($this->expectedZipPath);
    }

    /** @test */
    public function it_includes_files_from_the_local_disks_in_the_backup()
    {
        config()->set('backup.backup.source.files.include', [$this->getDiskRootPath('local')]);

        Storage::disk('local')->put('testing-file.txt', 'dummy content');

        $this->artisan('backup:run --only-files')->assertExitCode(0);

        $this->assertFileExistsInZip('local', $this->expectedZipPath, 'testing-file.txt');
    }

    /** @test */
    public function it_excludes_the_backup_destination_from_the_backup()
    {
        config()->set('backup.backup.source.files.include', [$this->getDiskRootPath('local')]);

        Storage::disk('local')->put('mysite/testing-file.txt', 'dummy content');

        $this->artisan('backup:run --only-files');

        $this->assertFileDoesntExistsInZip('local', $this->expectedZipPath, 'testing-file.txt');
    }

    /** @test */
    public function it_excludes_the_temporary_directory_from_the_backup()
    {
        $tempDirectoryPath = storage_path('app/backup-temp/temp');

        if (! file_exists($tempDirectoryPath)) {
            mkdir($tempDirectoryPath, 0777, true);
        }
        touch($tempDirectoryPath.DIRECTORY_SEPARATOR.'testing-file-temp.txt');

        $this->artisan('backup:run --only-files')->assertExitCode(0);

        $this->assertFileDoesntExistsInZip('local', $this->expectedZipPath, 'testing-file-temp.txt');
    }

    /** @test */
    public function it_can_backup_using_a_custom_filename_as_option()
    {
        $this->date = Carbon::create('2016', 1, 1, 9, 1, 1);

        Carbon::setTestNow($this->date);

        $filename = 'testing-filename.zip';

        $this->expectedZipPath = 'mysite/'.$filename;

        $this->artisan('backup:run', ['--only-files' => true, '--filename' => $filename])->assertExitCode(0);

        Storage::disk('local')->assertExists($this->expectedZipPath);
        Storage::disk('secondLocal')->assertExists($this->expectedZipPath);
    }

    /** @test */
    public function it_can_backup_to_a_specific_disk()
    {
        $this->artisan('backup:run --only-files --only-to-disk=secondLocal')->assertExitCode(0);

        Storage::disk('local')->assertMissing($this->expectedZipPath);
        Storage::disk('secondLocal')->assertExists($this->expectedZipPath);
    }

    /** @test */
    public function it_can_selectively_backup_db()
    {
        $this
            ->artisan('backup:run --only-db --db-name=db1')
            ->assertExitCode(0);

        Storage::disk('local')->assertExists($this->expectedZipPath);

        $this
            ->artisan('backup:run --only-db --db-name=db2')
            ->assertExitCode(0);

        Storage::disk('local')->assertExists($this->expectedZipPath);

        $this
            ->artisan('backup:run --only-db --db-name=db1 --db-name=db2')
            ->assertExitCode(0);

        Storage::disk('local')->assertExists($this->expectedZipPath);

        $this
            ->artisan('backup:run --only-db --db-name=wrongName')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_can_backup_twice_a_day_at_same_time_in_12h_clock()
    {
        // first backup
        $this->date = Carbon::create('2016', 1, 1, 9, 1, 1);

        Carbon::setTestNow($this->date);

        $this->expectedZipPath = 'mysite/2016-01-01-09-01-01.zip';

        $this->artisan('backup:run --only-files')->assertExitCode(0);

        Storage::disk('local')->assertExists($this->expectedZipPath);
        Storage::disk('secondLocal')->assertExists($this->expectedZipPath);

        // second backup
        $this->date = Carbon::create('2016', 1, 1, 21, 1, 1);

        Carbon::setTestNow($this->date);

        $this->expectedZipPath = 'mysite/2016-01-01-21-01-01.zip';

        $this->artisan('backup:run --only-files')->assertExitCode(0);

        Storage::disk('local')->assertExists($this->expectedZipPath);
        Storage::disk('secondLocal')->assertExists($this->expectedZipPath);
    }

    /** @test */
    public function it_will_fail_when_try_to_backup_only_the_files_and_only_the_db()
    {
        $resultCode = Artisan::call('backup:run --only-files --only-db');

        $this->assertEquals(1, $resultCode);

        $this->seeInConsoleOutput('Cannot use `only-db` and `only-files` together.');

        Storage::disk('local')->assertMissing($this->expectedZipPath);
        Storage::disk('secondLocal')->assertMissing($this->expectedZipPath);
    }

    /** @test */
    public function it_will_fail_when_trying_to_backup_a_non_existing_database()
    {
        //use an invalid db name to trigger failure
        Artisan::call('backup:run --only-files --only-db --db-name=wrongName');

        $this->seeInConsoleOutput('Backup failed');
    }

    /** @test */
    public function it_will_fail_when_trying_to_backup_to_an_non_existing_diskname()
    {
        $resultCode = Artisan::call('backup:run --only-to-disk=non-existing-disk');

        $this->assertEquals(1, $resultCode);

        $this->seeInConsoleOutput('There is no backup destination with a disk named');

        Storage::disk('local')->assertMissing($this->expectedZipPath);
        Storage::disk('secondLocal')->assertMissing($this->expectedZipPath);
    }

    /** @test */
    public function it_will_fail_when_there_are_no_files_to_be_backed_up()
    {
        config()->set('backup.backup.source.files.include', []);
        config()->set('backup.backup.source.databases', []);

        Artisan::call('backup:run');

        $this->seeInConsoleOutput('There are no files to be backed up');
    }

    /** @test */
    public function it_appends_the_database_type_to_backup_file_name_to_prevent_overwrite()
    {
        config()->set('backup.backup.source.databases', ['db1', 'db2']);

        $this->setUpDatabase($this->app);

        $this->artisan('backup:run --only-db')->assertExitCode(0);

        $this->assertFileExistsInZip('local', $this->expectedZipPath, 'sqlite-db1-database.sql');
        $this->assertFileExistsInZip('local', $this->expectedZipPath, 'sqlite-db2-database.sql');

        $this->assertFileExistsInZip('secondLocal', $this->expectedZipPath, 'sqlite-db1-database.sql');
        $this->assertFileExistsInZip('secondLocal', $this->expectedZipPath, 'sqlite-db2-database.sql');

        /*
         * Close the database connection to unlock the sqlite file for deletion.
         * This prevents the errors from other tests trying to delete and recreate the folder.
         */
        $this->app['db']->disconnect();
    }

    /** @test */
    public function it_should_trigger_the_backup_failed_event()
    {
        // use an invalid dbname to trigger failure
        $this->artisan('backup:run --only-db --db-name=wrongName')->assertExitCode(1);

        Event::assertDispatched(BackupHasFailed::class);
    }

    /** @test */
    public function it_should_omit_the_backup_failed_event_with_no_notifications_flag()
    {
        //use an invalid dbname to trigger failure
        $this->artisan('backup:run --only-db --db-name=wrongName --disable-notifications')->assertExitCode(1);

        Event::assertNotDispatched(BackupHasFailed::class);
    }

    /** @test */
    public function it_compresses_the_database_dump()
    {
        config()->set('backup.backup.source.databases', ['sqlite']);
        config()->set('backup.backup.database_dump_compressor', GzipCompressor::class);

        $this->setUpDatabase($this->app);

        $this->artisan('backup:run --only-db')->assertExitCode(0);

        $this->assertFileExistsInZip('local', $this->expectedZipPath, 'sqlite-sqlite-database.sql.gz');

        /*
         * Close the database connection to unlock the sqlite file for deletion.
         * This prevents the errors from other tests trying to delete and recreate the folder.
         */
        $this->app['db']->disconnect();
    }
}
