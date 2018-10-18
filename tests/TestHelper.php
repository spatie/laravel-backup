<?php

namespace Spatie\Backup\Test;

use DateTime;
use Illuminate\Filesystem\Filesystem;

class TestHelper
{
    /** @var \Illuminate\Filesystem\Filesystem */
    protected $filesystem;

    /**
     * Call method on object, useful for calling protected methods
     */
    public static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);

        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function initializeTempDirectory()
    {
        $this->initializeDirectory($this->getTempDirectory());
    }

    public function initializeDirectory(string $directory)
    {
        $this->filesystem->deleteDirectory($directory);

        $this->filesystem->makeDirectory($directory);

        $this->addGitignoreTo($directory);
    }

    public function addGitignoreTo(string $directory)
    {
        $fileName = "{$directory}/.gitignore";

        $fileContents = '*'.PHP_EOL.'!.gitignore';

        $this->filesystem->put($fileName, $fileContents);
    }

    public function getStubDirectory(): string
    {
        return __DIR__.'/stubs';
    }

    public function getStubDbDirectory(): string
    {
        return __DIR__.'/stubs-db';
    }

    public function getTempDirectory(): string
    {
        return __DIR__.'/temp';
    }

    public function createTempFileWithAge($fileName, DateTime $date, $contents = ''): string
    {
        $directory = $this->getTempDirectory().'/'.dirname($fileName);

        $this->filesystem->makeDirectory($directory, 0755, true, true);

        $fullPath = $this->getTempDirectory().'/'.$fileName;

        file_put_contents($fullPath, $contents);

        touch($fullPath, $date->getTimestamp());

        return $fullPath;
    }

    public function createTempFile1Mb(string $fileName, DateTime $date): string
    {
        $directory = $this->getTempDirectory().'/'.dirname($fileName);

        $this->filesystem->makeDirectory($directory, 0755, true, true);

        $sourceFile = $this->getStubDirectory().'/1Mb.file';

        $fullPath = $this->getTempDirectory().'/'.$fileName;

        copy($sourceFile, $fullPath);

        touch($fullPath, $date->getTimestamp());

        return $fullPath;
    }

    public function createSQLiteDatabase(string $fileName): string
    {
        $directory = $this->getTempDirectory().'/'.dirname($fileName);

        $this->filesystem->makeDirectory($directory, 0755, true, true);

        $sourceFile = $this->getStubDbDirectory().'/database.sqlite';

        $fullPath = $this->getTempDirectory().'/'.$fileName;

        copy($sourceFile, $fullPath);

        touch($fullPath);

        return $fullPath;
    }
}
