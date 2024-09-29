<?php

namespace Spatie\Backup\Contracts;

use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

/**
 * @mixin BaseTemporaryDirectory
 */
interface TemporaryDirectory
{
    public function __construct(string $location = '');
}
