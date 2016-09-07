<?php

namespace Spatie\Backup\Tasks\Backup;

use SplFileObject;

class Manifest
{
    /** @var string */
    protected $manifestPath;

    /**
     * @param string $manifestPath
     *
     * @return static
     */
    public static function create(string $manifestPath)
    {
        return new static($manifestPath);
    }

    public function __construct(string $manifestPath)
    {
        $this->manifestPath = $manifestPath;

        touch($manifestPath);
    }

    public function getPath(): string
    {
        return $this->manifestPath;
    }

    /**
     * @param array $filePaths
     *
     * @return $this
     */
    public function addFiles($filePaths)
    {
        if (is_string($filePaths)) {
            $filePaths = [$filePaths];
        }

        foreach ($filePaths as $filePath) {
            if (! empty($filePath)) {
                file_put_contents($this->manifestPath, $filePath.PHP_EOL, FILE_APPEND);
            }
        }

        return $this;
    }

    public function getFiles()
    {
        $file = new SplFileObject($this->getPath());

        while (! $file->eof()) {
            $filePath = $file->fgets();

            if (! empty($filePath)) {
                yield trim($filePath);
            }
        }
    }

    public function count(): int
    {
        $file = new SplFileObject($this->manifestPath, 'r');

        $file->seek(PHP_INT_MAX);

        return $file->key() + 1;
    }
}
