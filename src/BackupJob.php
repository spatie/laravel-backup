<?php

namespace Spatie\Backup;

class BackupJob
{
    public function __construct()
    {
    }

    public static function create() : BackupJob
    {
        return new static();
    }

    public function doNotIncludeAnyFiles()
    {
        return $this;
    }

    public function run()
    {
    }
}
