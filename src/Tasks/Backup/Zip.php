<?php

namespace Spatie\Backup\Tasks\Backup;

use ZipArchive;

class Zip
{
    /** @var \ZipArchive */
    protected $zipFile;

    /** @var int */
    protected $fileCount = 0;

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

        if (is_string($files)) {
            $files = [$files];
        }

        $this->open();

        foreach ($files as $file) {
            $this->zipFile->addFile($file, $nameInZip);
            ++$this->fileCount;
        }

        $this->close();

        return $this;
    }

    public function count()
    {
        return $this->fileCount;
    }
}
