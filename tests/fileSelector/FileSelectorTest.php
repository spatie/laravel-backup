<?php

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Spatie\Backup\FileHelpers\FileSelector;

class FileSelectorTest extends Orchestra\Testbench\TestCase {

    protected $path;

    protected $disk;

    protected $root;

    protected $testFilesPath;

    protected $fileSelector;

    public function setUp()
    {
        parent::setUp();

        $this->root = realpath('tests/_data/disk/root');

        $this->path = 'backups';

        $this->testFilesPath = realpath($this->root . '/' . $this->path);

        //make sure all files in our testdirectory are 5 days old
        foreach (scandir($this->testFilesPath) as $file)
        {

            touch($this->testFilesPath . '/' . $file, time() - (60 * 60 * 24 * 5));
        }

        $this->disk = new Illuminate\Filesystem\FilesystemAdapter(new Filesystem(new Local($this->root)));
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

        touch($this->testFilesPath . '/'  .$testFileName , time() - (60 * 60 * 24 * 10) + 60); //create a file that is 10 days and a minute old

        $oldFiles = $this->fileSelector->getFilesOlderThan((new DateTime())->sub(new DateInterval('P9D')), ['zip']);

        $this->assertTrue(in_array($this->path.'/'.$testFileName, $oldFiles));

        $oldFiles = $this->fileSelector->getFilesOlderThan((new DateTime())->sub(new DateInterval('P10D')), ['zip']);
        $this->assertFalse(in_array($this->path.'/'.$testFileName, $oldFiles));

        $oldFiles = $this->fileSelector->getFilesOlderThan((new DateTime())->sub(new DateInterval('P11D')), ['zip']);
        $this->assertFalse(in_array($this->path.'/'.$testFileName, $oldFiles));
    }

    /**
     * @test
     */
    public function it_excludes_files_outside_given_path()
    {
        $files = $this->fileSelector->getFilesOlderThan(new DateTime(), ['zip']);

        touch(realpath('tests/_data/disk/root/TomJones.zip'), time() - (60 * 60 * 24 * 10) + 60);

        $this->assertFalse(in_array($this->path . '/' . 'TomJones.zip', $files));
        $this->assertTrue(in_array($this->path . '/' . 'test.zip', $files));
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
