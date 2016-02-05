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

            $this->testHelper->createTempFileWithAge("mysite-com/test_{$date->format('Ymd')}.zip", $date);
        }

        Artisan::call('backup:clean');

        $this->assertTempFilesExist([
            'mysite-com/test_20131231.zip',
            'mysite-com/test_20141231.zip',
            'mysite-com/test_20150630.zip',
            'mysite-com/test_20150731.zip',
            'mysite-com/test_20150831.zip',
            'mysite-com/test_20150930.zip',
            'mysite-com/test_20151025.zip',
            'mysite-com/test_20151101.zip',
            'mysite-com/test_20151108.zip',
            'mysite-com/test_20151115.zip',
            'mysite-com/test_20151122.zip',
            'mysite-com/test_20151129.zip',
            'mysite-com/test_20151206.zip',
            'mysite-com/test_20151213.zip',
            'mysite-com/test_20151216.zip',
            'mysite-com/test_20151217.zip',
            'mysite-com/test_20151218.zip',
            'mysite-com/test_20151219.zip',
            'mysite-com/test_20151220.zip',
            'mysite-com/test_20151221.zip',
            'mysite-com/test_20151222.zip',
            'mysite-com/test_20151223.zip',
            'mysite-com/test_20151224.zip',
            'mysite-com/test_20151225.zip',
            'mysite-com/test_20151226.zip',
            'mysite-com/test_20151227.zip',
            'mysite-com/test_20151228.zip',
            'mysite-com/test_20151229.zip',
            'mysite-com/test_20151230.zip',
            'mysite-com/test_20151231.zip',
            'mysite-com/test_20160101.zip',
        ]);
    }

    /** @test */
    public function it_can_will_leave_non_zip_files_alone()
    {
        $this->testHelper->createTempFileWithAge('mysite-com/test1.txt', Carbon::now()->subDays(1));
        $this->testHelper->createTempFileWithAge('mysite-com/test2.txt', Carbon::now()->subDays(2));
        $this->testHelper->createTempFileWithAge('mysite-com/test1000.txt', Carbon::now()->subDays(1000));
        $this->testHelper->createTempFileWithAge('mysite-com/test2000.txt', Carbon::now()->subDays(2000));

        Artisan::call('backup:clean');

        $this->assertTempFilesExist([
            'mysite-com/test1.txt',
            'mysite-com/test2.txt',
            'mysite-com/test1000.txt',
            'mysite-com/test2000.txt',
        ]);
    }

    /** @test */
    public function it_wil_never_delete_the_youngest_backup()
    {
        foreach (range(5, 10) as $numberOfDays) {
            $date = Carbon::now()->subYears($numberOfDays);

            $this->testHelper->createTempFileWithAge("mysite-com/test_{$date->format('Ymd')}.zip", $date);
        }

        Artisan::call('backup:clean');

        $this->assertTempFilesExist(['mysite-com/test_20110101.zip']);

        $this->assertTempFilesNotExist([
            'mysite-com/test_20060101.zip',
            'mysite-com/test_20070101.zip',
            'mysite-com/test_20080101.zip',
            'mysite-com/test_20090101.zip',
            'mysite-com/test_200100101.zip',
        ]);

    }
}
