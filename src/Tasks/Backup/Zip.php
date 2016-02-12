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
     * @return \Spatie\Backup\Tasks\Backup\Zip|\Spatie\Backup\Zip
     */
    public static function create(string $pathToZip, $files = []) : Zip
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

    public function getPath() : string
    {
        return $this->pathToZip;
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
    public function add($files, string $nameInZip = null) : Zip
    {
        if (is_array($files)) {
            $nameInZip = null;
        }

        $this->open();

        collect($files)
            ->filter(function (string $file) {
               return is_file($file);
            })
            ->each(function (string $file) use ($nameInZip) {
                $this->zipFile->addFile($file, $nameInZip);
            });

        $this->close();

        return $this;
    }
}
