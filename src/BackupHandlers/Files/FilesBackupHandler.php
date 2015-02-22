<?php

namespace Spatie\Backup\BackupHandlers\Files;

use File;
use Spatie\Backup\BackupHandlers\BackupHandlerInterface;

class FilesBackupHandler implements BackupHandlerInterface {

    protected $includedFiles;

    protected $excludedFiles;

    /**
     * Returns an array of files which should be backed up.
     *
     * @return array
     */
    public function getFilesToBeBackedUp()
    {

        $filesToBeIncluded = $this->getAllPathFromFileArray($this->includedFiles);
        $filesToBeExcluded = $this->getAllPathFromFileArray($this->excludedFiles);

        return array_filter($filesToBeIncluded, function($file) use ($filesToBeExcluded) {
            return ! in_array($file, $filesToBeExcluded);
        });
    }

    /**
     * Set all files that should be included
     *
     * @param array $includedFiles
     * @return $this
     */
    public function setIncludedFiles($includedFiles)
    {
        $this->includedFiles = $includedFiles;

        return $this;
    }

    /**
     * Set all files that should be excluded
     *
     * @param array $excludedFiles
     * @return $this
     */
    public function setExcludedFiles($excludedFiles)
    {
        $this->excludedFiles = $excludedFiles;

        return $this;
    }

    public function getAllPathFromFileArray($fileArray)
    {
        $files = [];

        foreach($fileArray as $file)
        {
            if (File::isFile($file))
            {
                $files[] = $file;
            }

            if (File::isDirectory($file))
            {
                $files += File::allFiles($file);
            }
        }

        return array_unique(array_map(function($file) {
            return $file->getPathName();
        }, $files));
    }
}