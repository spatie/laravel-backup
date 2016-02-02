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
        $this->filesystem->deleteDirectory($this->getTempDirectory());

        $this->filesystem->makeDirectory($this->getTempDirectory());

        $this->addGitignoreTo($this->getTempDirectory());
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

    public function createTempFileWithAge($fileName, DateTime $date)
    {
        $directory = $this->getTempDirectory().'/'.dirname($fileName);

        $this->filesystem->makeDirectory($directory, 0755, true, true);

        touch($this->getTempDirectory().'/'.$fileName, $date->getTimeStamp());
    }
}
