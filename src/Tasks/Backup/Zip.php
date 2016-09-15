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

        foreach ($manifest->getFiles() as $file) {
            $zip->add($file, self::determineNameOfFileInZip($file, $pathToZip));
        }

        return $zip;
    }

    protected static function determineNameOfFileInZip(string $pathToFile, string $pathToZip)
    {
        $zipDirectory = pathinfo($pathToZip, PATHINFO_DIRNAME);

        $fileDirectory = pathinfo($pathToFile, PATHINFO_DIRNAME);

        if (starts_with($fileDirectory, $zipDirectory)) {
            return str_replace($zipDirectory, '', $pathToFile);
        }

        return $pathToFile;
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
        if ($this->fileCount === 0) {
            return 0;
        }

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
                $this->zipFile->addFile($file, $nameInZip).PHP_EOL;
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
