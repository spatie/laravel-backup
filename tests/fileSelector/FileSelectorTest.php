<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\FileHelpers\FileSelector;

class FileSelectorTest extends Orchestra\Testbench\TestCase {

    protected $fileSelector;
    protected $date;

    public function setUp()
    {
        parent::setUp();
        $this->date = new DateTime();
        $this->fileSelector = new fileSelector('tests/_data/backups', Storage::disk('local'));
    }

    public function test_if_files_are_filtered_on_extension()
    {
        $files = File::allFiles(realpath('tests/_data/backups'));

        $filteredFiles = $this->fileSelector->filterFilesOnExtension($files, 'zip');

        $this->assertNotEmpty($filteredFiles);

        $this->assertEmpty(
            array_filter($filteredFiles, function($file){
                return $file->getRelativePathname() == 'MariahCarey.php';
            })
        );
    }

    /*public function test_if_files_are_filtered_on_date()
    {
        $files = File::allFiles('tests/_data/backups');

        $filteredFiles = $this->fileSelector->filterFilesOnDate($files, $this->date);
    }


    public function test_if_correct_files_are_returned()
    {
        $files = $this->fileSelector->getFilesOlderThan($this->date, ['zip']);

    }*/
}
