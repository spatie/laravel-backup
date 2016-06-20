<?php

namespace Spatie\Backup\Test;

use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

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

    public function initializeDirectory($directory)
    {
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

    public function getMigrationDirectory()
    {
        return __DIR__.'/migrations';
    }

    
    public function createTempFileWithAge($fileName, DateTime $date, $contents = '')
    {
        $directory = $this->getTempDirectory().'/'.dirname($fileName);

        $this->filesystem->makeDirectory($directory, 0755, true, true);

        $fullPath = $this->getTempDirectory().'/'.$fileName;

        file_put_contents($fullPath, $contents);

        touch($fullPath, $date->getTimeStamp());
    }

    /**
     * @param string $directory
     * @param string $diskName
     */
    public function getFirstZipFileFromPath( $directory, $diskName )
    {

        $files = Storage::disk( $diskName )->files( $directory );

        $filePath = '';

        foreach ($files as $file) {
            if( pathinfo($file, PATHINFO_EXTENSION) == 'zip' )
            {
                $filePath = $file;
                break;
            }
        }
        
        if( empty($filePath))
        {
            return false;
        }

        $pathToZip = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix().$filePath;

        return $pathToZip;
    }

   
}
