<?php

namespace Spatie\Backup\Tests\Integration;

use ZipArchive;
use Spatie\Backup\Tests\TestHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Console\Kernel;
use Spatie\Backup\BackupServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use League\Flysystem\FileNotFoundException;
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

        $app['config']->set('backup.monitor_backups.0.health_checks', []);

        $app['config']->set('mail.driver', 'log');

        $app['config']->set('database.connections.db1', [
            'driver' => 'sqlite',
            'database' => $this->testHelper->createSQLiteDatabase('database1.sqlite'),
        ]);
        $app['config']->set('database.connections.db2', [
            'driver' => 'sqlite',
            'database' => $this->testHelper->createSQLiteDatabase('database2.sqlite'),
        ]);

        $app['config']->set('database.default', 'db1');

        $app['config']->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => $this->testHelper->getTempDirectory(),
        ]);

        $app['config']->set('filesystems.disks.secondLocal', [
            'driver' => 'local',
            'root' => $this->testHelper->getTempDirectory().'/secondDisk',
        ]);

        $app['config']->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        touch($this->testHelper->getTempDirectory().'/database.sqlite');

        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    public function assertFileExistsOnDisk(string $fileName, string $diskName)
    {
        $this->assertTrue($this->fileExistsOnDisk($fileName, $diskName), "Failed asserting that `{$fileName}` exists on disk `{$diskName}`");
    }

    public function assertFileNotExistsOnDisk(string $fileName, string $diskName)
    {
        $this->assertFalse($this->fileExistsOnDisk($fileName, $diskName), "Failed asserting that `{$fileName}` does not exist on disk `{$diskName}`");
    }

    public function fileExistsOnDisk(string $fileName, string $diskName): bool
    {
        try {
            Storage::disk($diskName)->getMetaData($fileName);

            return true;
        } catch (FileNotFoundException $exception) {
            return false;
        }
    }

    public function assertTempFilesExist(array $files)
    {
        foreach ($files as $file) {
            $path = $this->testHelper->getTempDirectory().'/'.$file;

            $this->assertFileExists($path);
        }
    }

    public function assertTempFilesNotExist(array $files)
    {
        foreach ($files as $file) {
            $path = $this->testHelper->getTempDirectory().'/'.$file;

            $this->assertFileNotExists($path);
        }
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

    protected function assertPathExists($path)
    {
        $this->assertTrue($this->pathExists($path), "Failed to assert that the directory `{$path}` exists");
    }

    protected function assertPathNotExists($path)
    {
        $this->assertFalse($this->pathExists($path), "Failed to assert that the directory `{$path}` does not exist");
    }

    protected function pathExists($path): bool
    {
        return is_dir($path) && file_exists($path);
    }

    protected function assertFileExistsInZip($zipPath, $filename)
    {
        $this->assertTrue($this->fileExistsInZip($zipPath, $filename), "Failed to assert that {$zipPath} contains a file name {$filename}");
    }

    protected function assertFileDoesntExistsInZip($zipPath, $filename)
    {
        $this->assertFalse($this->fileExistsInZip($zipPath, $filename), "Failed to assert that {$zipPath} doesn't contain a file name {$filename}");
    }

    protected function fileExistsInZip($zipPath, $filename): bool
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) === true) {
            return $zip->locateName($filename, ZipArchive::FL_NODIR) !== false;
        }

        return false;
    }
}
