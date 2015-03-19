<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Spatie\Backup\FileHelpers\FileSelector;

class FileSelectorTest extends Orchestra\Testbench\TestCase {

    protected $path;

    protected $disk;

    protected $fileSelector;

    public function setUp()
    {
        parent::setUp();
        $this->path = realpath('tests/_data/backups');

        //make sure all files in our testdirectory are 5 days old
        foreach (scandir($this->path) as $file)
        {
            touch($this->path . '/' . $file, time() - (60 * 60 * 24 * 5));
        }

        $this->disk = new Illuminate\Filesystem\FilesystemAdapter(new Filesystem(new Local($this->path)));
        $this->fileSelector = new FileSelector($this->disk, $this->path);
    }

    /**
     * @test
     */
    public function it_returns_only_files_with_the_specified_extensions()
    {
        $oldFiles = $this->fileSelector->getFilesOlderThan(new DateTime(), ['zip']);

        $this->assertNotEmpty($oldFiles);

        $this->assertFalse(in_array('MariahCarey.php', $oldFiles));
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_extensions_are_specified()
    {
        $oldFiles = $this->fileSelector->getFilesOlderThan(new DateTime(), ['']);

        $this->assertEmpty($oldFiles);
    }

    /**
     * @test
     */
    public function it_gets_files_older_than_the_given_date()
    {
        $testFileName = 'test_it_gets_files_older_than_the_given_date.zip';

        touch($this->path . '/'  .$testFileName , time() - (60 * 60 * 24 * 10) + 60); //create a file that is 10 days and a minute old

        $oldFiles = $this->fileSelector->getFilesOlderThan((new DateTime())->sub(new DateInterval('P9D')), ['zip']);
        $this->assertTrue(in_array($testFileName, $oldFiles));

        $oldFiles = $this->fileSelector->getFilesOlderThan((new DateTime())->sub(new DateInterval('P10D')), ['zip']);
        $this->assertFalse(in_array($testFileName, $oldFiles));

        $oldFiles = $this->fileSelector->getFilesOlderThan((new DateTime())->sub(new DateInterval('P11D')), ['zip']);
        $this->assertFalse(in_array($testFileName, $oldFiles));

    }

    /**
     * Call artisan command and return code.
     *
     * @param string $command
     * @param array $parameters
     *
     * @return int
     */
    public function artisan($command, $parameters = [])
    {

    }
}
