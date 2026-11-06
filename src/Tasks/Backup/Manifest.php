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

        $handle = null;

        try {
            foreach ($filePaths as $filePath) {
                if (empty($filePath)) {
                    continue;
                }

                if ($handle === null) {
                    $handle = fopen($this->manifestPath, 'a');

                    if ($handle === false) {
                        throw new RuntimeException("Could not open manifest file at `{$this->manifestPath}`.");
                    }
                }

                $line = $filePath.PHP_EOL;
                $offset = 0;
                $length = strlen($line);

                while ($offset < $length) {
                    $written = fwrite($handle, substr($line, $offset));

                    if ($written === false || $written === 0) {
                        throw new RuntimeException("Could not write manifest file at `{$this->manifestPath}`.");
                    }

                    $offset += $written;
                }
            }
        } finally {
            if (is_resource($handle)) {
                if (! fclose($handle)) {
                    throw new RuntimeException("Could not close manifest file at `{$this->manifestPath}`.");
                }
            }
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

        if ($file->getSize() === 0) {
            return 0;
        }

        $file->seek(PHP_INT_MAX);

        return $file->key();
    }
}
