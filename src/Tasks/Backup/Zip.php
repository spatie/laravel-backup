<?php

namespace Spatie\Backup\Tasks\Backup;

use ZipArchive;
use Illuminate\Support\Str;
use Spatie\Backup\Helpers\Format;

class Zip
{
    /** @var \ZipArchive */
    protected $zipFile;

    /** @var int */
    protected $fileCount = 0;

    /** @var string */
    protected $pathToZip;

    /** @var string */
    protected $password;

    public static function createForManifest(Manifest $manifest, string $pathToZip): self
    {
        $zip = new static($pathToZip);

        $zip->open();

        $zip->setPassword(config('backup.backup.password', null));

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

    public static function formatZipFilename(string $filename): string
    {
        return ltrim($filename, DIRECTORY_SEPARATOR);
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
     * @param string|null $password
     * @return Zip
     */
    public function setPassword(?string $password): self
    {
        if (! empty($password) && ! method_exists(ZipArchive::class, 'setEncryptionName')) {
            consoleOutput()->info('Password encryption requires PHP 7.2 >= and libzip-dev >= 1.2.0');

            return $this;
        }

        $this->password = $password;

        return $this;
    }

    /**
     * @param string $nameInZip
     *
     * @return Zip
     */
    protected function passwordProtectFile(string $nameInZip): self
    {
        if (empty($this->password)) {
            return $this;
        }

        if (! $this->zipFile->setEncryptionName($nameInZip, ZipArchive::EM_AES_256, $this->password)) {
            consoleOutput()->error("Cannot password protect {$nameInZip}");
        }

        return $this;
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
                $localName = self::formatZipFilename($nameInZip ?? $file);

                if ($this->zipFile->addFile($file, $localName).PHP_EOL) {
                    $this->passwordProtectFile($localName);
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
