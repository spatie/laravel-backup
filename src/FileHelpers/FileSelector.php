<?php namespace Spatie\Backup\FileHelpers;

use DateTime;

class FileSelector {

    protected $disk;

    public function __construct($disk)
    {
        $this->disk = $disk;
    }

    /**
     * Get all files older than $date
     * Only files with an extension present in $onlyIncludeFilesWithExtension will be returned
     *
     * @param DateTime $date
     * @param array $onlyIncludeFilesWithExtension
     * @return array
     */
    public function getFilesOlderThan(DateTime $date, array $onlyIncludeFilesWithExtension)
    {
        $allFiles = $this->disk->allFiles();

        foreach($onlyIncludeFilesWithExtension as $extension)
        {
            $backupFiles = $this->filterFilesOnExtension($allFiles, $extension);
        }

        return $this->filterFilesOnDate($backupFiles, $date);
    }

    /**
     * Return $files with included $extension
     *
     * @param $backupFiles
     * @param $extension
     * @return array
     */
    private function filterFilesOnExtension($backupFiles, $extension)
    {
        return array_filter($backupFiles, function($file) use($extension){
            return strtolower(pathinfo($file, PATHINFO_EXTENSION)) == $extension;
        });
    }

    /**
     * Filter files on given $date
     *
     * @param $files
     * @param DateTime $date
     * @return array
     */
    private function filterFilesOnDate($files, DateTime $date)
    {
        return array_filter($files, function($file) use($date){
            return $this->disk->lastModified($file) < $date->getTimeStamp();
        });
    }
}
