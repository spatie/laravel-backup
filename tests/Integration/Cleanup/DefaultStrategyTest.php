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

        $app['config']->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => $this->testHelper->getTempDirectory(),
        ]);
    }


    /** @test */
    public function it_can_remove_old_backups_from_the_backup_directory()
    {
        foreach (range(0, 1000) as $numberOfDays) {
            $date = Carbon::now()->subDays($numberOfDays);

            $this->testHelper->createTempFileWithAge("backups/test_{$date->format('Ymd')}.zip", $date);
        }

        Artisan::call('backup:clean');

        $this->assertTempFilesExist([
            'backups/test_20131231.zip',
            'backups/test_20141231.zip',
            'backups/test_20150630.zip',
            'backups/test_20150731.zip',
            'backups/test_20150831.zip',
            'backups/test_20150930.zip',
            'backups/test_20151025.zip',
            'backups/test_20151101.zip',
            'backups/test_20151108.zip',
            'backups/test_20151115.zip',
            'backups/test_20151122.zip',
            'backups/test_20151129.zip',
            'backups/test_20151206.zip',
            'backups/test_20151213.zip',
            'backups/test_20151216.zip',
            'backups/test_20151217.zip',
            'backups/test_20151218.zip',
            'backups/test_20151219.zip',
            'backups/test_20151220.zip',
            'backups/test_20151221.zip',
            'backups/test_20151222.zip',
            'backups/test_20151223.zip',
            'backups/test_20151224.zip',
            'backups/test_20151225.zip',
            'backups/test_20151226.zip',
            'backups/test_20151227.zip',
            'backups/test_20151228.zip',
            'backups/test_20151229.zip',
            'backups/test_20151230.zip',
            'backups/test_20151231.zip',
            'backups/test_20160101.zip',
        ]);
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

    /** @test */
    public function it_wil_never_delete_the_youngest_backup()
    {
        foreach (range(5, 10) as $numberOfDays) {
            $date = Carbon::now()->subYears($numberOfDays);

            $this->testHelper->createTempFileWithAge("backups/test_{$date->format('Ymd')}.zip", $date);
        }

        Artisan::call('backup:clean');

        $this->assertTempFilesExist(['backups/test_20110101.zip']);

        $this->assertTempFilesNotExist([
            'backups/test_20060101.zip',
            'backups/test_20070101.zip',
            'backups/test_20080101.zip',
            'backups/test_20090101.zip',
            'backups/test_200100101.zip',
        ]);

    }
}
