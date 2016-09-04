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
    public static function create(string $pathToZip, $files = []): Zip
    {
        $zip = new static($pathToZip);

        $zip->add($files);

        return $zip;
    }

    public function __construct(string $pathToZip)
    {
        $this->zipFile = new ZipArchive();

        $this->pathToZip = $pathToZip;

        $this->open($pathToZip);
    }

    public function getPath(): string
    {
        return $this->pathToZip;
    }

    public function getSize(): int
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
    public function add($files, string $nameInZip = null): Zip
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

    public function count(): int
    {
        return $this->fileCount;
    }
}
