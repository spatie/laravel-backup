<?php

namespace Spatie\Backup\BackupHandlers;

interface BackupHandlerInterface
{

    /**
     * Returns an array of files which should be backed up.
     *
     * @return array
     */
    public function getFilesToBeBackedUp();
}
