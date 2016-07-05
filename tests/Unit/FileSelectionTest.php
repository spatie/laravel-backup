<?php

namespace Spatie\Backup\Test\Unit;

use Spatie\Backup\Tasks\Backup\FileSelection;
use Spatie\Backup\Test\TestHelper;

class FileSelectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @string
     */
    protected $sourceDirectory;

    public function setUp()
    {
        parent::setUp();

        $this->sourceDirectory = (new TestHelper())->getStubDirectory();
    }

    /** @test */
    public function it_can_select_all_the_files_in_a_directory_and_subdirectories()
    {
        $fileSelection = new FileSelection($this->sourceDirectory);

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
            ]),
            iterator_to_array($fileSelection->getSelectedFiles())
        );
    }

    /** @test */
    public function it_can_exclude_files_from_a_given_subdirectory()
    {
        $fileSelection = (new FileSelection($this->sourceDirectory))
                        ->excludeFilesFrom("{$this->sourceDirectory}/directory1");

        $this->assertSame(
            $this->getTestFiles([
                'directory2/directory1/file1.txt',
                'file1.txt',
                'file2.txt',
                'file3.txt',
            ]),
            iterator_to_array($fileSelection->getSelectedFiles())
        );
    }

    /** @test */
    public function it_can_exclude_files_with_wildcards_from_a_given_subdirectory()
    {
        $fileSelection = (new FileSelection($this->sourceDirectory))
            ->excludeFilesFrom("{$this->sourceDirectory}/*/directory1");

        $this->assertSame(
            $this->getTestFiles([
                'directory1/file1.txt',
                'directory1/file2.txt',
                'file1.txt',
                'file2.txt',
                'file3.txt',
            ]),
            iterator_to_array($fileSelection->getSelectedFiles())
        );
    }

    /** @test */
    public function it_can_select_files_from_multiple_directories()
    {
        $fileSelection = (new FileSelection([
            $this->sourceDirectory.'/directory1/directory1',
            $this->sourceDirectory.'/directory2/directory1',
        ]));

        $this->assertSame(
            $this->getTestFiles([
                'directory1/directory1/file1.txt',
                'directory1/directory1/file2.txt',
                'directory2/directory1/file1.txt',
            ]),
            iterator_to_array($fileSelection->getSelectedFiles()));
    }

    /** @test */
    public function it_can_exclude_files_from_multiple_directories()
    {
        $fileSelection = (new FileSelection($this->sourceDirectory))
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
            ]),
            iterator_to_array($fileSelection->getSelectedFiles())
        );
    }

    /** @test */
    public function it_returns_an_empty_array_when_not_specifying_any_directories()
    {
        $fileSelection = new FileSelection('');

        $this->assertEmpty(iterator_to_array($fileSelection->getSelectedFiles()));
    }

    /** @test */
    public function it_returns_an_empty_array_if_everything_is_excluded()
    {
        $fileSelection = (new FileSelection($this->sourceDirectory))
            ->excludeFilesFrom($this->sourceDirectory);

        $this->assertEmpty(iterator_to_array($fileSelection->getSelectedFiles()));
    }

    /** @test */
    public function it_provides_a_factory_method()
    {
        $fileSelection = FileSelection::create();

        $this->assertInstanceOf(FileSelection::class, $fileSelection);
    }

    /** @test */
    public function memory_test_thing()
    {
        $files = FileSelection::create([__DIR__ . '/../../../blender/node_modules'])->getSelectedFiles();
    }

    /**
     * @param array $relativePaths
     *
     * @return array
     */
    protected function getTestFiles(array $relativePaths)
    {
        $absolutePaths = array_map(function ($path) {
             return "{$this->sourceDirectory}/{$path}";
         }, $relativePaths);

        return $absolutePaths;
    }
}
