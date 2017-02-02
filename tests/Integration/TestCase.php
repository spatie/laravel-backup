<?php

namespace Spatie\Backup\Test\Integration;

use Event;
use Exception;
use Spatie\Backup\Test\TestHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Console\Kernel;
use Spatie\Backup\BackupServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use League\Flysystem\FileNotFoundException;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /** @var \Spatie\Backup\Test\TestHelper */
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

        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('mail.driver', 'log');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->testHelper->getTempDirectory().'/database.sqlite',
            'prefix' => '',
        ]);

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
        file_put_contents($this->testHelper->getTempDirectory().'/database.sqlite', null);

        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        TestModel::create(['name' => 'test']);
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

    protected function expectsEvent($eventClassName)
    {
        Event::listen($eventClassName, function ($event) use ($eventClassName) {
            $this->firedEvents[] = $eventClassName;
        });

        $this->beforeApplicationDestroyed(function () use ($eventClassName) {
            $firedEvents = isset($this->firedEvents) ? $this->firedEvents : [];

            if (! in_array($eventClassName, $firedEvents)) {
                throw new Exception("Event {$eventClassName} not fired");
            }
        });
    }

    protected function doesNotExpectEvent($eventClassName)
    {
        Event::listen($eventClassName, function ($event) use ($eventClassName) {
            throw new Exception("Event {$eventClassName} unexpectingly fired");
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

    protected function assertFileExistsInZip($zipPath, $filename) {

        $this->assertTrue($this->fileExistsInZip($zipPath, $filename), "Failed to assert that {$zipPath} contains a file name {$filename}");
    }

    protected function assertFileDoesntExistsInZip($zipPath, $filename) {

        $this->assertFalse($this->fileExistsInZip($zipPath, $filename), "Failed to assert that {$zipPath} doesn't contain a file name {$filename}");
    }

    protected function fileExistsInZip($zipPath, $filename) {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === TRUE)
        {
            return ($zip->locateName($filename,\ZipArchive::FL_NODIR) !== false);
        }
        return false;
    }
}
