<?php

namespace Spatie\Backup\Tests;

use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Backup\BackupServiceProvider;
use ZipArchive;

abstract class TestCase extends Orchestra
{
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
        $this->initializeTempDirectory();

        config()->set('backup.monitor_backups.0.health_checks', []);

        config()->set('mail.driver', 'log');

        config()->set('database.connections.db1', [
            'driver' => 'sqlite',
            'database' => $this->createSQLiteDatabase('database1.sqlite'),
        ]);

        config()->set('database.connections.db2', [
            'driver' => 'sqlite',
            'database' => $this->createSQLiteDatabase('database2.sqlite'),
        ]);

        config()->set('database.default', 'db1');

        Storage::fake('local');
        Storage::fake('secondLocal');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        touch($this->getTempDirectory().'/database.sqlite');

        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    protected function seeInConsoleOutput($expectedText)
    {
        $consoleOutput = $this->app[Kernel::class]->output();

        $this->assertStringContainsString(
            $expectedText,
            $consoleOutput,
            "Did not see `{$expectedText}` in console output: `$consoleOutput`"
        );
    }

    protected function doNotSeeInConsoleOutput($unExpectedText)
    {
        $consoleOutput = $this->app[Kernel::class]->output();

        $this->assertNotContains(
            $unExpectedText,
            $consoleOutput,
            "Did not expect to see `{$unExpectedText}` in console output: `$consoleOutput`"
        );
    }

    protected function assertFileExistsInZip(string $diskName, string $zipPath, string $fileName)
    {
        $this->assertTrue(
            $this->fileExistsInZip($diskName, $zipPath, $fileName),
            "Failed to assert that {$zipPath} contains a file name {$fileName}"
        );
    }

    protected function assertFileDoesntExistsInZip(string $diskName, string $zipPath, string $fileName)
    {
        $this->assertFalse(
            $this->fileExistsInZip($diskName, $zipPath, $fileName),
            "Failed to assert that {$zipPath} doesn't contain a file name {$fileName}"
        );
    }

    protected function fileExistsInZip(string $diskName, string $zipPath, string $fileName): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($this->getFullDiskPath($diskName, $zipPath)) === true) {
            return $zip->locateName($fileName, ZipArchive::FL_NODIR) !== false;
        }

        return false;
    }

    protected function createFileOnDisk(string $diskName, string $filePath, DateTime $date): string
    {
        Storage::disk($diskName)->put($filePath, 'dummy content');

        touch($this->getFullDiskPath($diskName, $filePath), $date->getTimestamp());

        return $filePath;
    }

    protected function create1MbFileOnDisk(string $diskName, string $filePath, DateTime $date)
    {
        $sourceFile = $this->getStubDirectory().'/1Mb.file';

        Storage::disk($diskName)->put($filePath, file_get_contents($sourceFile));

        touch($this->getFullDiskPath($diskName, $filePath), $date->getTimestamp());
    }

    protected function getFullDiskPath(string $diskName, string $filePath): string
    {
        return $this->getDiskRootPath($diskName).DIRECTORY_SEPARATOR.$filePath;
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

    public function createSQLiteDatabase(string $fileName): string
    {
        $directory = $this->getTempDirectory().'/'.dirname($fileName);

        File::makeDirectory($directory, 0755, true, true);

        $sourceFile = $this->getStubDbDirectory().'/database.sqlite';

        $fullPath = $this->getTempDirectory().'/'.$fileName;

        copy($sourceFile, $fullPath);

        touch($fullPath);

        return $fullPath;
    }

    public function getStubDbDirectory(): string
    {
        return __DIR__.'/stubs-db';
    }

    public function getTempDirectory(): string
    {
        return __DIR__.'/temp';
    }

    public function initializeTempDirectory()
    {
        $this->initializeDirectory($this->getTempDirectory());
    }

    public function initializeDirectory(string $directory)
    {
        File::deleteDirectory($directory);

        File::makeDirectory($directory);

        $this->addGitignoreTo($directory);
    }

    public function removeTempDirectory()
    {
        return $this->filesystem->deleteDirectory($this->getTempDirectory());
    }

    public function addGitignoreTo(string $directory)
    {
        $fileName = "{$directory}/.gitignore";

        $fileContents = '*'.PHP_EOL.'!.gitignore';

        File::put($fileName, $fileContents);
    }
}
