<?php

namespace Spatie\Backup\Tasks\Backup;

use Spatie\Backup\Config\Config;
use Spatie\Backup\Exceptions\BackupFailed;
use Spatie\Backup\Helpers\Format;
use ZipArchive;

class Zip
{
    protected ZipArchive $zipFile;

    protected int $fileCount = 0;

    protected Config $config;

    protected ?int $encryptionAlgorithm = null;

    public function __construct(protected string $pathToZip)
    {
        $this->zipFile = new ZipArchive;
        $this->config = app(Config::class);

        if ($this->config->backup->password !== null) {
            $this->encryptionAlgorithm = $this->config->backup->encryption->algorithm();
        }

        $this->open();
    }

    public static function createForManifest(Manifest $manifest, string $pathToZip): self
    {
        $config = app(Config::class);

        $relativePath = $config->backup->source->files->relativePath
            ? rtrim($config->backup->source->files->relativePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
            : false;

        $zip = new static($pathToZip);

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

        if (str_starts_with($fileDirectory, $zipDirectory)) {
            return substr($pathToFile, strlen($zipDirectory));
        }

        if ($relativePath && $relativePath !== DIRECTORY_SEPARATOR && str_starts_with($fileDirectory, $relativePath)) {
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
        $result = $this->zipFile->open($this->pathToZip, ZipArchive::CREATE);

        if ($result !== true) {
            throw BackupFailed::from(new \Exception("Failed to open zip file at '{$this->pathToZip}'. ZipArchive error code: {$result}"));
        }

        $password = $this->config->backup->password;

        if ($this->encryptionAlgorithm !== null && $password !== null) {
            $this->zipFile->setPassword($password);
        }
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
                $fileNameInZip = ltrim($nameInZip ?: $file, DIRECTORY_SEPARATOR);

                $this->zipFile->addFile($file, $fileNameInZip);

                $this->zipFile->setCompressionName($fileNameInZip, $compressionMethod, $compressionLevel);

                if ($this->encryptionAlgorithm !== null) {
                    $result = $this->zipFile->setEncryptionName($fileNameInZip, $this->encryptionAlgorithm);

                    if ($result !== true) {
                        throw BackupFailed::from(new \Exception("Failed to set encryption for '{$fileNameInZip}' in zip file at '{$this->pathToZip}'."));
                    }
                }
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
