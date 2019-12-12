<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Str;
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
        $zipDirectory = pathinfo($pathToZip, PATHINFO_DIRNAME);

        $fileDirectory = pathinfo($pathToFile, PATHINFO_DIRNAME);

        if (Str::startsWith($fileDirectory, $zipDirectory)) {
            return str_replace($zipDirectory, '', $pathToFile);
        }

        return $pathToFile;
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

    public function size(): float
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
            if (is_dir($file)) {
                $this->zipFile->addEmptyDir($file);
            }

            if (is_file($file)) {
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
