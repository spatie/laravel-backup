<?php

namespace Spatie\Backup\Tasks\Backup;

use Countable;
use Generator;
use SplFileObject;

class Manifest implements Countable
{
    protected string $manifestPath;

    public static function create(string $manifestPath): self
    {
        return new static($manifestPath);
    }

    public function __construct(string $manifestPath)
    {
        $this->manifestPath = $manifestPath;

        touch($manifestPath);
    }

    public function path(): string
    {
        return $this->manifestPath;
    }

    public function addFiles(array | string | Generator $filePaths): self
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

    public function files(): Generator | array
    {
        $file = new SplFileObject($this->path());

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

        return $file->key();
    }
}
