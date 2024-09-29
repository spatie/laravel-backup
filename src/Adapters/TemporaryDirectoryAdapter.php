<?php

namespace Spatie\Backup\Adapters;

use Spatie\Backup\Contracts\TemporaryDirectory;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class TemporaryDirectoryAdapter implements TemporaryDirectory
{
    protected BaseTemporaryDirectory $temporaryDirectory;

    public function __construct(string $location = '')
    {
        $this->temporaryDirectory = new BaseTemporaryDirectory($location);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->temporaryDirectory->{$name}(...$arguments);
    }
}
