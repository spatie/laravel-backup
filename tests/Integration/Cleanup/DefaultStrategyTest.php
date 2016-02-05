<?php

namespace Spatie\Backup\Test\Integration;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class DefaultStrategyTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2016, 1, 1));

        $this->testHelper->initializeTempDirectory();

        $app = $this->app;

        foreach(range(0, 1000) as $numberOfDays) {
            $date = Carbon::now()->subDays($numberOfDays);

            $this->testHelper->createTempFileWithAge("backups/test_{$date->format('Ymd')}.zip", $date);
        }

        $app['config']->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => $this->testHelper->getTempDirectory(),
        ]);
    }

    /** @test */
    public function it_can_remove_old_backup_from_the_backup_directory()
    {
        Artisan::call('backup:clean');

        dd('stop');
    }

    /** @test */
    public function it_can_will_leave_non_zip_files_alone()
    {
        $this->testHelper->createTempFileWithAge('backups/test1.txt', Carbon::now()->subDays(1));
        $this->testHelper->createTempFileWithAge('backups/test2.txt', Carbon::now()->subDays(2));
        $this->testHelper->createTempFileWithAge('backups/test1000.txt', Carbon::now()->subDays(1000));
        $this->testHelper->createTempFileWithAge('backups/test2000.txt', Carbon::now()->subDays(2000));

        Artisan::call('backup:clean');

        $this->assertTempFilesExist([
            'backups/test1.txt',
            'backups/test2.txt',
            'backups/test1000.txt',
            'backups/test2000.txt',
        ]);
    }
}
