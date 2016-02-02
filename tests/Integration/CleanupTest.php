<?php

namespace Spatie\Backup\Test\Integration;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class CleanupTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->testHelper->initializeTempDirectory();

        $app = $this->app;

        $app['config']->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => $this->testHelper->getTempDirectory(),
        ]);
    }

    /** @test */
    public function it_can_remove_old_backup_from_the_backup_directory()
    {
        $this->testHelper->createTempFileWithAge('backups/test80.zip', Carbon::now()->subDays(80));
        $this->testHelper->createTempFileWithAge('backups/test89.zip', Carbon::now()->subDays(89));
        $this->testHelper->createTempFileWithAge('backups/test90-.zip', Carbon::now()->subDays(90)->addHour(1));
        $this->testHelper->createTempFileWithAge('backups/test90+.zip', Carbon::now()->subDays(90)->subHour(1));
        $this->testHelper->createTempFileWithAge('backups/test91.zip', Carbon::now()->subDays(91));
        $this->testHelper->createTempFileWithAge('backups/test100.zip', Carbon::now()->subDays(100));

        Artisan::call('backup:clean');

        $this->assertTempFilesExist([
            'backups/test80.zip',
            'backups/test89.zip',
            'backups/test90-.zip',
        ]);
        $this->assertTempFilesNotExist([
            'backups/test90+.zip',
            'backups/test91.zip',
            'backups/test100.zip',
        ]);
    }

    /** @test */
    public function it_can_will_leave_non_zip_files_alone()
    {
        $this->testHelper->createTempFileWithAge('backups/test80.txt', Carbon::now()->subDays(80));
        $this->testHelper->createTempFileWithAge('backups/test89.txt', Carbon::now()->subDays(89));
        $this->testHelper->createTempFileWithAge('backups/test90-.txt', Carbon::now()->subDays(90)->addHour(1));
        $this->testHelper->createTempFileWithAge('backups/test90+.txt', Carbon::now()->subDays(90)->subHour(1));
        $this->testHelper->createTempFileWithAge('backups/test91.txt', Carbon::now()->subDays(91));
        $this->testHelper->createTempFileWithAge('backups/test100.txt', Carbon::now()->subDays(100));

        Artisan::call('backup:clean');

        $this->assertTempFilesExist([
            'backups/test80.txt',
            'backups/test89.txt',
            'backups/test90-.txt',
            'backups/test90+.txt',
            'backups/test91.txt',
            'backups/test100.txt',
        ]);
    }
}
