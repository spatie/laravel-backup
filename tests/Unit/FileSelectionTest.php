<?php

namespace Spatie\Backup\Test\Unit;

use PHPUnit\Framework\TestCase;
use Spatie\Backup\Test\TestHelper;
use Spatie\Backup\Tasks\Backup\FileSelection;

class FileSelectionTest extends TestCase
{
    /** @string */
    protected $sourceDirectory;

    public function setUp()
    {
        parent::setUp();

        $this->sourceDirectory = (new TestHelper())->getStubDirectory();
    }

    protected function assertSameArrayContent($a, $b)
    {
        $this->assertTrue($this->arrays_are_similar($a, $b));
    }

    /**
     * Determine if two associative arrays are similar.
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    protected function arrays_are_similar($a, $b)
    {
        // if the indexes don't match, return immediately
        if (count(array_diff_assoc($a, $b))) {
            return false;
        }
        // we know that the indexes, but maybe not values, match.
        // compare the values between the two arrays
        foreach ($a as $k => $v) {
            if ($v !== $b[$k]) {
                return false;
            }
        }
        // we have identical indexes, and no unequal values
        return true;
    }

    /** @test */
    public function it_can_select_all_the_files_in_a_directory_and_subdirectories()
    {
        $fileSelection = new FileSelection($this->sourceDirectory);

        $testFiles = $this->getTestFiles([
                '.dotfile',
                '1Mb.file',
                'directory1/directory1/file1.txt',
                'directory1/directory1/file2.txt',
                'directory1/file1.txt',
                'directory1/file2.txt',
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
                'directory1/file1.txt',
                'directory1/file2.txt',
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
