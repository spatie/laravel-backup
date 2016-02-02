<?php

namespace Spatie\Backup\Test\Integration;

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

        //$this->setUpDatabase($this->app);
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

    public function fileWithExtensionExistsInDirectoryOnDisk(string $extension, string $directory, string $diskName)
    {
        $disk = Storage::disk($diskName);

        $files = $disk->files($directory);

        $fileCount = collect($files)->filter(function (string $fileName) use ($extension) {
            return pathinfo($fileName, PATHINFO_EXTENSION) == $extension;
        })
        ->count();

        $this->assertTrue($fileCount > 0, "There are no files with extension `{$extension}` on `{$directory}` on `{$diskName}`");
    }
}
