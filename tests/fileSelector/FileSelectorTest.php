<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Spatie\Backup\FileHelpers\FileSelector;
use Symfony\Component\Finder\SplFileInfo;

class FileSelectorTest extends Orchestra\Testbench\TestCase {

    protected $fileSelector;
    protected $date;

    public function setUp()
    {
        parent::setUp();
        $this->date = new DateTime();

        $this->disk = new Illuminate\Filesystem\FilesystemAdapter(new Filesystem(new Local(realpath('tests/_data/backups'))));
        $this->fileSelector = new FileSelector($this->disk);
    }

    public function test_if_files_are_filtered_on_extension()
    {

        $files = array_map(function($file) {
            return new SplFileInfo($file, $file, $file);
        }, $this->disk->allFiles());

        $filteredFiles = $this->fileSelector->filterFilesOnExtension($files, 'zip');

        $this->assertNotEmpty($filteredFiles);

        $this->assertEmpty(
            array_filter($filteredFiles, function($file){
                return $file->getRelativePathname() == 'MariahCarey.php';
            })
        );
    }

    public function test_if_files_are_filtered_on_date()
    {
        $files = array_map(function($file) {
            return new SplFileInfo($file, $file, $file);
        }, $this->disk->allFiles());

        $filteredFiles = $this->fileSelector->filterFilesOnDate($files, $this->date);
    }

    public function test_if_correct_files_are_returned()
    {
        $files = $this->fileSelector->getFilesOlderThan($this->date, ['zip']);
    }

}
