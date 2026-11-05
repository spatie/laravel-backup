<?php

namespace Spatie\Backup\Tasks\Backup;

use Countable;
use Generator;
use RuntimeException;
use SplFileObject;

class Manifest implements Countable
{
    public static function create(string $manifestPath): self
    {
        return new static($manifestPath);
    }

    public function __construct(protected string $manifestPath)
    {
        touch($this->manifestPath);
    }

    public function path(): string
    {
        return $this->manifestPath;
    }

    /**
     * @param  Generator|string|array<string>  $filePaths
     */
    public function addFiles(array|string|Generator $filePaths): self
    {
        if (is_string($filePaths)) {
            $filePaths = [$filePaths];
        }

        $handle = fopen($this->manifestPath, 'a');

        if ($handle === false) {
            throw new RuntimeException("Could not open manifest file at `{$this->manifestPath}`.");
        }

        try {
            foreach ($filePaths as $filePath) {
                if (! empty($filePath)) {
                    fwrite($handle, $filePath.PHP_EOL);
                }
            }
        } finally {
            fclose($handle);
        }

        return $this;
    }

    public function files(): Generator|array
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
