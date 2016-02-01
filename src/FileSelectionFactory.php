<?php

namespace Spatie\Backup;

class FileSelectionFactory
{
    public static function noFiles()
    {
        return new FileSelection([]);
    }

    public function create(array $config)
    {
        $files = FileSelection::create($config)
            ->excludeFilesFrom($this->excludedPaths);
    }
}
