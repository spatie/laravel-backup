<?php namespace Spatie\Backup\FileHelpers;

use Storage;
use DateTime;

class FileSelector {

    protected $disk;
    protected $path;

    public function __construct($path, $disk)
    {
        $this->path = $path;
        $this->disk = $disk;
    }

    /**
     * Get all files older than $date, filtered on $includedExtensions
     *
     * @param DateTime $date
     * @param array $includedExtensions
     * @return array
     */
    public function getFilesOlderThan(DateTime $date, array $includedExtensions)
    {
        foreach($includedExtensions as $extension)
        {
            $backupFiles = $this->filterFilesOnExtension($this->disk->allFiles($this->path), $extension);
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
    public function filterFilesOnExtension($backupFiles, $extension)
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
    public function filterFilesOnDate($files, DateTime $date)
    {
        return array_filter($files, function($file) use($date){
            return Storage::lastModified($file) < $date->getTimeStamp();
        });
    }
}
