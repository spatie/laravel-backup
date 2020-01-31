<?php

namespace Spatie\Backup\Tests;

use Spatie\Backup\Tasks\Backup\FileSelection;

class FileSelectionTest extends TestCase
{
    /** @string */
    protected $sourceDirectory;

    public function setUp(): void
    {
        parent::setUp();

        $this->sourceDirectory = $this->getStubDirectory();
    }

    protected function assertSameArrayContent($expected, $actual, $message = '')
    {
        $this->assertCount(count($expected), array_intersect($expected, $actual), $message);
    }

    /** @test */
    public function it_can_select_all_the_files_in_a_directory_and_subdirectories()
    {
        $fileSelection = new FileSelection($this->sourceDirectory);

        $testFiles = $this->getTestFiles([
            '.dotfile',
            '1Mb.file',
            'directory1',
            'directory1/directory1',
            'directory1/directory1/file1.txt',
            'directory1/directory1/file2.txt',
            'directory1/file1.txt',
            'directory1/file2.txt',
            'directory2',
            'directory2/directory1',
            'directory2/directory1/file1.txt',
            'file1.txt',
            'file2.txt',
            'file3.txt',
        ]);
        $selectedFiles = iterator_to_array($fileSelection->selectedFiles());

        $this->assertSameArray($testFiles, $selectedFiles);
    }

    /** @test */
    public function it_can_exclude_files_from_a_given_subdirectory()
    {
        $fileSelection = (new FileSelection($this->sourceDirectory))
                        ->excludeFilesFrom("{$this->sourceDirectory}/directory1");

        $testFiles = $this->getTestFiles([
            '.dotfile',
            '1Mb.file',
            'directory2',
            'directory2/directory1',
            'directory2/directory1/file1.txt',
            'file1.txt',
            'file2.txt',
            'file3.txt',
        ]);
        $selectedFiles = iterator_to_array($fileSelection->selectedFiles());

        $this->assertSameArray($testFiles, $selectedFiles);
    }

    /** @test */
    public function it_can_exclude_files_with_wildcards_from_a_given_subdirectory()
    {
        $fileSelection = (new FileSelection($this->sourceDirectory))
            ->excludeFilesFrom("{$this->sourceDirectory}/*/directory1");

        $testFiles = $this->getTestFiles([
            '.dotfile',
            '1Mb.file',
            'directory1',
            'directory1/file1.txt',
            'directory1/file2.txt',
            'directory2',
            'file1.txt',
            'file2.txt',
            'file3.txt',
        ]);
        $selectedFiles = iterator_to_array($fileSelection->selectedFiles());

        $this->assertSameArray($testFiles, $selectedFiles);
    }

    /** @test */
    public function it_can_select_files_from_multiple_directories()
    {
        $fileSelection = (new FileSelection([
            $this->sourceDirectory.'/directory1/directory1',
            $this->sourceDirectory.'/directory2/directory1',
        ]));

        $this->assertSameArrayContent(
            $this->getTestFiles([
                'directory1/directory1/file2.txt',
                'directory1/directory1/file1.txt',
                'directory2/directory1/file1.txt',
            ]),
            iterator_to_array($fileSelection->selectedFiles())
        );
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

        $testFiles = $this->getTestFiles([
            '.dotfile',
            '1Mb.file',
            'directory1',
            'directory1/file1.txt',
            'directory1/file2.txt',
            'file1.txt',
            'file3.txt',
        ]);
        $selectedFiles = iterator_to_array($fileSelection->selectedFiles());

        $this->assertSameArray($testFiles, $selectedFiles);
    }

    /** @test */
    public function it_returns_an_empty_array_when_not_specifying_any_directories()
    {
        $fileSelection = new FileSelection();

        $this->assertEmpty(iterator_to_array($fileSelection->selectedFiles()));
    }

    /** @test */
    public function it_returns_an_empty_array_if_everything_is_excluded()
    {
        $fileSelection = (new FileSelection($this->sourceDirectory))
            ->excludeFilesFrom($this->sourceDirectory);

        $this->assertEmpty(iterator_to_array($fileSelection->selectedFiles()));
    }

    /** @test */
    public function it_can_select_a_single_file()
    {
        $fileSelection = (new FileSelection([
            $this->sourceDirectory.'/.dotfile',
        ]));

        $this->assertSame(
            $this->getTestFiles([
                '.dotfile',
            ]),
            iterator_to_array($fileSelection->selectedFiles())
        );
    }

    /** @test */
    public function it_provides_a_factory_method()
    {
        $fileSelection = FileSelection::create();

        $this->assertInstanceOf(FileSelection::class, $fileSelection);
    }

    protected function getTestFiles(array $relativePaths): array
    {
        $absolutePaths = array_map(function ($path) {
            return "{$this->sourceDirectory}/{$path}";
        }, $relativePaths);

        return $absolutePaths;
    }

    protected function assertSameArray(array $array1, array $array2)
    {
        sort($array1);
        sort($array2);
        $this->assertSame($array1, $array2);
    }
}
