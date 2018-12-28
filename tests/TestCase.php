<?php

namespace Spatie\Backup\Tests;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Schema;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Console\Kernel;
use Spatie\Backup\BackupServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** @var \Spatie\Backup\Tests\TestHelper */
    protected $testHelper;

    public function setUp()
    {
        $this->testHelper = new TestHelper();

        parent::setUp();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            BackupServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->testHelper->initializeTempDirectory();

        config()->set('backup.monitor_backups.0.health_checks', []);

        config()->set('mail.driver', 'log');

        config()->set('database.connections.db1', [
            'driver' => 'sqlite',
            'database' => $this->testHelper->createSQLiteDatabase('database1.sqlite'),
        ]);
        config()->set('database.connections.db2', [
            'driver' => 'sqlite',
            'database' => $this->testHelper->createSQLiteDatabase('database2.sqlite'),
        ]);

        config()->set('database.default', 'db1');

        Storage::fake('local');
        Storage::fake('secondLocal');

        config()->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        touch($this->testHelper->getTempDirectory().'/database.sqlite');

        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    protected function seeInConsoleOutput($expectedText)
    {
        $consoleOutput = $this->app[Kernel::class]->output();

        $this->assertContains($expectedText, $consoleOutput, "Did not see `{$expectedText}` in console output: `$consoleOutput`");
    }

    protected function doNotSeeInConsoleOutput($unExpectedText)
    {
        $consoleOutput = $this->app[Kernel::class]->output();

        $this->assertNotContains($unExpectedText, $consoleOutput, "Did not expect to see `{$unExpectedText}` in console output: `$consoleOutput`");
    }

    protected function assertFileExistsInZip(string $diskName, string $zipPath, string $fileName)
    {
        $this->assertTrue($this->fileExistsInZip($diskName, $zipPath, $fileName), "Failed to assert that {$zipPath} contains a file name {$fileName}");
    }

    protected function assertFileDoesntExistsInZip(string $diskName, string $zipPath, string $fileName)
    {
        $this->assertFalse($this->fileExistsInZip($diskName, $zipPath, $fileName), "Failed to assert that {$zipPath} doesn't contain a file name {$fileName}");
    }

    protected function fileExistsInZip(string $diskName, string $zipPath, string $fileName): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($this->getFullDiskPath($diskName, $zipPath)) === true) {
            return $zip->locateName($fileName, ZipArchive::FL_NODIR) !== false;
        }

        return false;
    }

    protected function createFileOnDisk(string $diskName, string $filePath, DateTime $date, $content = 'content of testfile'): string
    {
        Storage::disk($diskName)->put($filePath, $content);

        touch($this->getFullDiskPath($diskName, $filePath), $date->getTimestamp());

        return $filePath;
    }

    protected function createFile1MbOnDisk(string $diskName, string $filePath, DateTime $date)
    {
        $sourceFile = $this->getStubDirectory().'/1Mb.file';

        Storage::disk($diskName)->put($filePath, file_get_contents($sourceFile));

        touch($this->getFullDiskPath($diskName, $filePath), $date->getTimestamp());
    }

    protected function getFullDiskPath(string $diskName, string $filePath): string
    {
        return $this->getDiskRootPath($diskName) . DIRECTORY_SEPARATOR . $filePath;
    }

    protected function getDiskRootPath(string $diskName): string
    {
        return Storage::disk($diskName)->getDriver()->getAdapter()->getPathPrefix();
    }

    public function setNow($year, $month, $day, $hour = 0, $minutes = 0, $seconds = 0)
    {
        $date = Carbon::create($year, $month, $day, $hour, $minutes, $seconds);

        Carbon::setTestNow($date);
    }

    public function getStubDirectory(): string
    {
        return __DIR__.'/stubs';
    }
}
