<?php

namespace Spatie\Backup\Tasks\Backup;

use Spatie\Backup\Helpers\Format;
use ZipArchive;

class Zip
{
    /** @var \ZipArchive */
    protected $zipFile;

    /** @var int */
    protected $fileCount = 0;

    /** @var string */
    protected $pathToZip;

    public static function createForManifest(Manifest $manifest, string $pathToZip): Zip
    {
        $zip = new static($pathToZip);

        $zip->add($manifest->getFiles());

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

    public function getHumanReadableSize(): string
    {
        return Format::getHumanReadableSize($this->getSize());
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
     * @param string $nameInZip
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

            if (file_exists($file)) {
                $this->zipFile->addFile($file, $nameInZip) . PHP_EOL;

            }
            $this->fileCount++;

        }

        $this->close();

        return $this;
    }

    public function count(): int
    {
        return $this->fileCount;
    }
}
