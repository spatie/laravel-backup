<?php

namespace Spatie\Skeleton\Test\Unit;

use Spatie\Backup\FileFinder;

class FileFinderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @string
     */
    protected $sourceDirectory;

    public function setUp()
    {
        parent::setUp();

        $this->sourceDirectory = realpath(__DIR__.'/../testfiles/source');
    }

    /** @test */
    public function it_can_select_all_the_files_in_a_directory_and_subdirectories()
    {
        $fileFinder = new FileFinder($this->sourceDirectory);

        $this->assertSame(
            $this->getTestFiles([
                'directory1/directory1/file1.txt',
                'directory1/directory1/file2.txt',
                'directory1/file1.txt',
                'directory1/file2.txt',
                'directory2/directory1/file1.txt',
                'file1.txt',
                'file2.txt',
                'file3.txt',
            ]), $fileFinder->getSelectedFiles());
    }

    /** @test */
    public function it_can_exclude_files_from_a_given_subdirectory()
    {
        $fileFinder = (new FileFinder($this->sourceDirectory))
                        ->excludeFilesFrom("{$this->sourceDirectory}/directory1");

        $this->getTestFiles([
            'directory2/directory1/file1.txt',
            'file1.txt',
            'file2.txt',
            'file3.txt',
        ], $fileFinder->getSelectedFiles());
    }

    /** @test */
    public function it_can_select_files_from_multiple_directories()
    {
        $fileFinder = (new FileFinder([
            $this->sourceDirectory.'/directory1/directory1',
            $this->sourceDirectory.'/directory2/directory1',
        ]));

        $this->assertSame(
        $this->getTestFiles([
            'directory1/directory1/file1.txt',
            'directory1/directory1/file2.txt',
            'directory2/directory1/file1.txt',
        ]), $fileFinder->getSelectedFiles());
    }

    /** @test */
    public function it_can_exclude_files_from_multiple_directories()
    {
        $fileFinder = (new FileFinder($this->sourceDirectory))
        ->excludeFilesFrom($this->getTestFiles([
            'directory1/directory1',
            'directory2',
            'file2.txt',
            ]));

        $this->assertSame(
            $this->getTestFiles([
                'directory1/file1.txt',
                'directory1/file2.txt',
                'file1.txt',
                'file3.txt',
            ]), $fileFinder->getSelectedFiles());
    }

    public function getTestFiles(array $relativePaths) : array
    {
        $absolutePaths = array_map(function (string $path) {
             return "{$this->sourceDirectory}/{$path}";
         }, $relativePaths);

        return $absolutePaths;
    }
}
