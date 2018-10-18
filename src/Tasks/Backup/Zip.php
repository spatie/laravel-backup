<?php

namespace Spatie\Backup\Tasks\Backup;

use ZipArchive;
use Spatie\Backup\Helpers\Format;

class Zip
{
    /** @var \ZipArchive */
    protected $zipFile;

    /** @var int */
    protected $fileCount = 0;

    /** @var string */
    protected $pathToZip;

    public static function createForManifest(Manifest $manifest, string $pathToZip): self
    {
        $zip = new static($pathToZip);

        $zip->open();

        foreach ($manifest->files() as $file) {
            $zip->add($file, self::determineNameOfFileInZip($file, $pathToZip));
        }

        $zip->close();

        return $zip;
    }

    protected static function determineNameOfFileInZip(string $pathToFile, string $pathToZip)
    {
        $result = $pathToFile;

        $zipDirectory = pathinfo($pathToZip, PATHINFO_DIRNAME);
        $fileDirectory = pathinfo($pathToFile, PATHINFO_DIRNAME);

        if (starts_with($fileDirectory, $zipDirectory)) {
            $result = str_replace($zipDirectory, '', $result);
        }

        /* remove drive letter */
        $result = preg_match('/^[A-Z]:/i', $result) ? substr($result, 2) : $result;

        /* only use / path separator */
        $result = str_replace('\\', '/', $result);

        return $result;
    }

    public function __construct(string $pathToZip)
    {
        $this->zipFile = new ZipArchive();

        $this->pathToZip = $pathToZip;

        $this->open();
    }

    public function path(): string
    {
        return $this->pathToZip;
    }

    public function size(): int
    {
        if ($this->fileCount === 0) {
            return 0;
        }

        return filesize($this->pathToZip);
    }

    public function humanReadableSize(): string
    {
        return Format::humanReadableSize($this->size());
    }

    public function open()
    {
        $this->zipFile->open($this->pathToZip, ZipArchive::CREATE);
    }

    public function close()
    {
        $this->zipFile->close();
    }

    /**
     * @param string|array $files
     * @param string $nameInZip
     *
     * @return \Spatie\Backup\Tasks\Backup\Zip
     */
    public function add($files, string $nameInZip = null): self
    {
        if (is_array($files)) {
            $nameInZip = null;
        }

        if (is_string($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if (file_exists($file)) {
                $this->zipFile->addFile($file, ltrim($nameInZip, DIRECTORY_SEPARATOR)).PHP_EOL;
            }
            $this->fileCount++;
        }

        return $this;
    }

    public function count(): int
    {
        return $this->fileCount;
    }
}
