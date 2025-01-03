<?php

namespace Spatie\Backup\Tasks\Backup;

use Illuminate\Support\Str;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Helpers\Format;
use ZipArchive;

class Zip
{
    protected ZipArchive $zipFile;

    protected int $fileCount = 0;

    protected Config $config;

    public function __construct(protected string $pathToZip)
    {
        $this->zipFile = new ZipArchive;
        $this->config = app(Config::class);

        $this->open();
    }

    public static function createForManifest(Manifest $manifest, string $pathToZip): self
    {
        $config = app(Config::class);

        $relativePath = $config->backup->source->files->relativePath
            ? rtrim($config->backup->source->files->relativePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
            : false;

        $zip = new static($pathToZip);

        $zip->open();

        foreach ($manifest->files() as $file) {
            $zip->add($file, self::determineNameOfFileInZip($file, $pathToZip, $relativePath));
        }

        $zip->close();

        return $zip;
    }

    protected static function determineNameOfFileInZip(string $pathToFile, string $pathToZip, string $relativePath): string
    {
        $fileDirectory = pathinfo($pathToFile, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;

        $zipDirectory = pathinfo($pathToZip, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;

        if (Str::startsWith($fileDirectory, $zipDirectory)) {
            return substr($pathToFile, strlen($zipDirectory));
        }

        if ($relativePath && $relativePath != DIRECTORY_SEPARATOR && Str::startsWith($fileDirectory, $relativePath)) {
            return substr($pathToFile, strlen($relativePath));
        }

        return $pathToFile;
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

    public function open(): void
    {
        $this->zipFile->open($this->pathToZip, ZipArchive::CREATE);
    }

    public function close(): void
    {
        $this->zipFile->close();
    }

    public function add(string|iterable $files, ?string $nameInZip = null): self
    {
        if (is_array($files)) {
            $nameInZip = null;
        }

        if (is_string($files)) {
            $files = [$files];
        }

        $compressionMethod = $this->config->backup->destination->compressionMethod;
        $compressionLevel = $this->config->backup->destination->compressionLevel;

        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->zipFile->addEmptyDir(ltrim($nameInZip ?: $file, DIRECTORY_SEPARATOR));
            }

            if (is_file($file)) {
                $this->zipFile->addFile($file, ltrim((string) $nameInZip, DIRECTORY_SEPARATOR));

                $this->zipFile->setCompressionName(
                    ltrim($nameInZip ?: $file, DIRECTORY_SEPARATOR),
                    $compressionMethod,
                    $compressionLevel
                );
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
