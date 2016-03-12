<?php

namespace Spatie\Backup\Test;

use DateTime;
use Illuminate\Filesystem\Filesystem;

class TestHelper
{
    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public function initializeTempDirectory()
    {
        $this->initializeDirectory($this->getTempDirectory());
    }

    public function initializeDirectory($directory) {

        $this->filesystem->deleteDirectory($directory);

        $this->filesystem->makeDirectory($directory);

        $this->addGitignoreTo($directory);
    }

    public function addGitignoreTo($directory)
    {
        $fileName = "{$directory}/.gitignore";

        $fileContents = '*'.PHP_EOL.'!.gitignore';

        $this->filesystem->put($fileName, $fileContents);
    }

    public function getStubDirectory()
    {
        return __DIR__.'/stubs';
    }

    public function getTempDirectory()
    {
        return __DIR__.'/temp';
    }

    public function createTempFileWithAge($fileName, DateTime $date, $contents = '')
    {
        $directory = $this->getTempDirectory().'/'.dirname($fileName);

        $this->filesystem->makeDirectory($directory, 0755, true, true);

        $fullPath = $this->getTempDirectory().'/'.$fileName;

        file_put_contents($fullPath, $contents);

        touch($fullPath, $date->getTimeStamp());
    }
}
