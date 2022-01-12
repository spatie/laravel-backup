<?php

namespace Spatie\Backup\Tests\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Tests\TestCase;
use Spatie\DbDumper\Compressors\GzipCompressor;
use ZipArchive;

class BackupCommandTest extends TestCase
{
    protected Carbon $date;

    protected string $expectedZipPath;

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
        config()->set('backup.backup.source.files.include', [$this->getFullDiskPath('local', 'testing-file.txt')]);

        Storage::disk('local')->put('mysite/testing-file.txt', 'dummy content');

        $this->artisan('backup:run --only-files');

        $this->assertFileDoesntExistsInZip('local', $this->expectedZipPath, 'testing-file.txt');
    }

    /** @test */
    public function it_can_backup_using_relative_path()
    {
        config()->set('backup.backup.source.files.include', [$this->getStubDirectory()]);
        config()->set('backup.backup.source.files.exclude', [$this->getStubDirectory('.dot'), $this->getStubDirectory('file'), $this->getStubDirectory('file1.txt.txt')]);
        config()->set('backup.backup.source.files.relative_path', $this->getStubDirectory());

        $testFiles = [
            '.dotfile',
            'archive.zip',
            '1Mb.file',
            'directory1/',
            'directory1/directory1/',
            'directory1/directory1/file1.txt',
            'directory1/directory1/file2.txt',
            'directory1/file1.txt',
            'directory1/file2.txt',
            'directory2/',
            'directory2/directory1/',
            'directory2/directory1/file1.txt',
            'file1.txt',
            'file2.txt',
            'file3.txt',
        ];

        $this->artisan('backup:run --only-files')->assertExitCode(0);

        $zipFiles = [];
        $zip = new ZipArchive();
        $zip->open(Storage::disk('local')->path($this->expectedZipPath));
        foreach (range(0, $zip->numFiles - 1) as $i) {
            $zipFiles[] = $zip->statIndex($i)['name'];
        }
        $zip->close();
        sort($testFiles);
        sort($zipFiles);

        $this->assertSame($testFiles, $zipFiles);
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
    public function it_avoid_full_path_on_database_backup()
    {
        config()->set('backup.backup.source.databases', ['db1']);

        $this->setUpDatabase($this->app);

        $this->artisan('backup:run --only-db')->assertExitCode(0);

        $this->assertExactPathExistsInZip('local', $this->expectedZipPath, 'db-dumps/sqlite-db1-database.sql');

        /*
         * Close the database connection to unlock the sqlite file for deletion.
         * This prevents the errors from other tests trying to delete and recreate the folder.
         */
        $this->app['db']->disconnect();
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
    public function it_renames_database_dump_file_extension_when_specified()
    {
        config()->set('backup.backup.source.databases', ['db1', 'db2']);
        config()->set('backup.backup.database_dump_file_extension', 'backup');

        $this->setUpDatabase($this->app);

        $this->artisan('backup:run --only-db')->assertExitCode(0);

        $this->assertFileExistsInZip('local', $this->expectedZipPath, 'sqlite-db1-database.backup');
        $this->assertFileExistsInZip('local', $this->expectedZipPath, 'sqlite-db2-database.backup');
        $this->assertFileDoesntExistsInZip('local', $this->expectedZipPath, 'sqlite-db1-database.sql');
        $this->assertFileDoesntExistsInZip('local', $this->expectedZipPath, 'sqlite-db2-database.sql');

        $this->assertFileExistsInZip('secondLocal', $this->expectedZipPath, 'sqlite-db1-database.backup');
        $this->assertFileExistsInZip('secondLocal', $this->expectedZipPath, 'sqlite-db2-database.backup');
        $this->assertFileDoesntExistsInZip('secondLocal', $this->expectedZipPath, 'sqlite-db1-database.sql');
        $this->assertFileDoesntExistsInZip('secondLocal', $this->expectedZipPath, 'sqlite-db2-database.sql');

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

    /** @test */
    public function it_will_encrypt_backup_when_notifications_are_disabled()
    {
        config()->set('backup.backup.password', '24dsjF6BPjWgUfTu');
        config()->set('backup.backup.source.databases', ['db1']);

        $this->artisan('backup:run --disable-notifications --only-db --db-name=db1 --only-to-disk=local')->assertExitCode(0);
        Storage::disk('local')->assertExists($this->expectedZipPath);

        $zip = new ZipArchive();
        $zip->open(Storage::disk('local')->path($this->expectedZipPath));
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame(ZipArchive::EM_AES_256, $zip->statIndex(0)['encryption_method']);
        $zip->close();

        Event::assertNotDispatched(BackupZipWasCreated::class);
    }
}
