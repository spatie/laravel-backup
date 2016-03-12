<?php

namespace Spatie\Backup\Test\Integration;

use Event;
use Exception;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\Backup\BackupServiceProvider;
use Spatie\Backup\Test\TestHelper;

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

    /**
     * @param string $extension
     * @param string $directory
     * @param string $diskName
     */
    public function assertFileWithExtensionExistInDirectoryOnDisk($extension, $directory, $diskName)
    {
        $fileCount = $this->countFilesWithExtensionExistsInDirectoryOnDisk($extension, $directory, $diskName);

        $this->assertTrue($fileCount > 0, "There are no files with extension `{$extension}` on `{$directory}` on `{$diskName}`");
    }

    /**
     * @param string $extension
     * @param string $directory
     * @param string $diskName
     */
    public function assertFileWithExtensionDoNotExistInDirectoryOnDisk($extension, $directory, $diskName)
    {
        $fileCount = $this->countFilesWithExtensionExistsInDirectoryOnDisk($extension, $directory, $diskName);

        $this->assertTrue($fileCount === 0, "There are files with extension `{$extension}` on `{$directory}` on `{$diskName}`");
    }

    /**
     * @param string $extension
     * @param string $directory
     * @param string $diskName
     *
     * @return int
     */
    protected function countFilesWithExtensionExistsInDirectoryOnDisk($extension, $directory, $diskName)
    {
        $disk = Storage::disk($diskName);

        $files = $disk->files($directory);

        $fileCount = collect($files)->filter(function ($fileName) use ($extension) {
            return pathinfo($fileName, PATHINFO_EXTENSION) == $extension;
        })
            ->count();

        return $fileCount;
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

            if (!in_array($eventClassName, $firedEvents)) {
                throw new Exception("Event {$eventClassName} not fired");
            }
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
}
