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
        $files = File::allFiles('tests/_data/backups');

        $filteredFiles = $this->fileSelector->filterFilesOnExtension($files, 'zip');

        $this->assertEquals('ElvisPresley.zip', $filteredFiles[0]->getRelativePathname());
        $this->assertEquals('JohnnyCash.zip', $filteredFiles[1]->getRelativePathname());
        $this->assertEquals('test.zip', $filteredFiles[3]->getRelativePathname());

        // Skips to key 3 because (fortunately) MariahCarey is filtered out!
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
