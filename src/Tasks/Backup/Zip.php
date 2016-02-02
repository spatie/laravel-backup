<?php

namespace Spatie\Backup\Tasks\Backup;

use ZipArchive;

class Zip
{
    /**
     * @var \Spatie\Backup\ZipArchive
     */
    protected $zipFile;

    /**
     * @param string       $pathToZip
     * @param string|array $files
     *
     * @return \Spatie\Backup\Zip
     */
    public static function create(string $pathToZip, $files) : Zip
    {
        $zip = new static($pathToZip);

        $zip->add($files);

        return $zip;
    }

    public function __construct(string $pathToZip)
    {
        $this->zipFile = new ZipArchive();

        $this->zipFile->open($pathToZip, ZipArchive::CREATE);
    }

    /**
     * @param string|array $files
     */
    public function add($files) : Zip
    {
        collect($files)
            ->filter(function (string $file) {
               return is_file($file);
            })
            ->each(function (string $file) {
                $this->zipFile->addFile($file);
            });

        $this->zipFile->close();

        return $this;
    }

    /*
     * Get path to the zipfile
     */
    public function getPath() : string
    {
        return $this->zipFile->filename;
    }
}
