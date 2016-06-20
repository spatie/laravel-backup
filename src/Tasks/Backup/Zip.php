<?php

namespace Spatie\Backup\Tasks\Backup;

use ZipArchive;

class Zip
{
    /** @var \ZipArchive */
    protected $zipFile;

    /** @var string */
    protected $pathToZip;

    /**
     * @param string       $pathToZip
     * @param string|array $files
     *
     * @return \Spatie\Backup\Tasks\Backup\Zip
     */
    public static function create($pathToZip, $files = [])
    {
        $zip = new static($pathToZip);

        $zip->add($files);

        return $zip;
    }

    /**
     * @param string $pathToZip
     */
    public function __construct($pathToZip)
    {
        $this->zipFile = new ZipArchive();

        $this->pathToZip = $pathToZip;

        $this->open($pathToZip);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->pathToZip;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return filesize($this->pathToZip);
    }

    protected function open()
    {
        $this->zipFile->open($this->pathToZip, ZipArchive::CREATE);
    }

    protected function close()
    {
        $this->zipFile->close();
    }

    /**
     * @param string|array $files
     * @param string       $nameInZip
     *
     * @return \Spatie\Backup\Tasks\Backup\Zip
     */
    public function add($files, $nameInZip = null)
    {
        if (is_array($files)) {
            $nameInZip = null;
        }

        $this->open();

        collect($files)
            ->filter(function ($file) {
               return is_file($file);
            })
            ->each(function ($file) use ($nameInZip) {
                $this->zipFile->addFile($file, $nameInZip);
            });

        $this->close();

        return $this;
    }

    /**
     * @param string $fileName
     *
     * @return boolean
     */
    public function fileExists($fileName)
    {
        $this->open();

        $index = $this->zipFile->locateName($fileName);
    
        $this->close();

        return $index !== false;
    }

     /**
     * @param string $fileName
     *
     * @return boolean
     */
    public function getFile($fileName)
    {
        $this->open();

        $stream = $this->zipFile->getStream($fileName);
        $this->close();

        return $stream;
    }
}
