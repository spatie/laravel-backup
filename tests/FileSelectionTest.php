<?php

use Spatie\Backup\Tasks\Backup\FileSelection;

beforeEach(function () {
    $this->sourceDirectory = $this->getStubDirectory();
});

it('can select all the files in a directory and subdirectories', function () {
    $fileSelection = new FileSelection($this->sourceDirectory);

    $testFiles = getTestFiles([
        '.dot',
        '.dot/file1.txt',
        '.dotfile',
        'archive.zip',
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
        'file',
        'file1.txt',
        'file1.txt.txt',
        'file2.txt',
        'file3.txt',
    ]);
    $selectedFiles = iterator_to_array($fileSelection->selectedFiles());

    assertSameArray($testFiles, $selectedFiles);
});

it('can exclude files from a given subdirectory', function () {
    $fileSelection = (new FileSelection($this->sourceDirectory))
        ->excludeFilesFrom("{$this->sourceDirectory}/directory1");

    $testFiles = getTestFiles([
        '.dot',
        '.dot/file1.txt',
        '.dotfile',
        'archive.zip',
        '1Mb.file',
        'directory2',
        'directory2/directory1',
        'directory2/directory1/file1.txt',
        'file',
        'file1.txt',
        'file1.txt.txt',
        'file2.txt',
        'file3.txt',
    ]);
    $selectedFiles = iterator_to_array($fileSelection->selectedFiles());

    assertSameArray($testFiles, $selectedFiles);
});

it('can exclude files with wildcards from a given subdirectory', function () {
    $fileSelection = (new FileSelection($this->sourceDirectory))
        ->excludeFilesFrom(getTestFiles([
            '*/file1.txt',
            '*/directory1',
        ]));

    $testFiles = getTestFiles([
        '.dot',
        '.dotfile',
        'archive.zip',
        '1Mb.file',
        'directory1',
        'directory1/file2.txt',
        'directory2',
        'file',
        'file1.txt', // it is kept because it is not in a directory /dir/file1.txt
        'file1.txt.txt',
        'file2.txt',
        'file3.txt',
    ]);
    $selectedFiles = iterator_to_array($fileSelection->selectedFiles());

    assertSameArray($testFiles, $selectedFiles);
});

it('can select files from multiple directories', function () {
    $fileSelection = (new FileSelection([
        $this->sourceDirectory.'/directory1/directory1',
        $this->sourceDirectory.'/directory2/directory1',
    ]));

    assertSameArrayContent(
        getTestFiles([
            'directory1/directory1/file2.txt',
            'directory1/directory1/file1.txt',
            'directory2/directory1/file1.txt',
        ]),
        iterator_to_array($fileSelection->selectedFiles())
    );
});

it('can exclude files from multiple directories', function () {
    $fileSelection = (new FileSelection($this->sourceDirectory))
        ->excludeFilesFrom(getTestFiles([
            'directory1/directory1',
            'directory2',
            'file2.txt',
        ]));

    $testFiles = getTestFiles([
        '.dot',
        '.dot/file1.txt',
        '.dotfile',
        'archive.zip',
        '1Mb.file',
        'directory1',
        'directory1/file1.txt',
        'directory1/file2.txt',
        'file',
        'file1.txt',
        'file1.txt.txt',
        'file3.txt',
    ]);
    $selectedFiles = iterator_to_array($fileSelection->selectedFiles());

    assertSameArray($testFiles, $selectedFiles);
});

it('returns an empty array when not specifying any directories', function () {
    $fileSelection = new FileSelection;

    expect(iterator_to_array($fileSelection->selectedFiles()))->toBeEmpty();
});

it('returns an empty array if everything is excluded', function () {
    $fileSelection = (new FileSelection($this->sourceDirectory))
        ->excludeFilesFrom($this->sourceDirectory);

    expect(iterator_to_array($fileSelection->selectedFiles()))->toBeEmpty();
});

it('can select a single file', function () {
    $fileSelection = (new FileSelection([
        $this->sourceDirectory.'/.dotfile',
    ]));

    $this->assertSame(
        getTestFiles([
            '.dotfile',
        ]),
        iterator_to_array($fileSelection->selectedFiles())
    );
});

it('provides a factory method', function () {
    $fileSelection = FileSelection::create();

    expect($fileSelection)->toBeInstanceOf(FileSelection::class);
});

it('preserves links to included targets inside excluded directories', function (bool $followLinks) {
    $this->initializeTempDirectory();
    $root = $this->getTempDirectory();
    mkdir("{$root}/source");
    mkdir("{$root}/source/excluded");
    mkdir("{$root}/outside");
    file_put_contents("{$root}/outside/keep.txt", 'keep');
    file_put_contents("{$root}/source/excluded/skip.txt", 'skip');
    symlink("{$root}/outside/keep.txt", "{$root}/source/excluded/file-link.txt");
    symlink("{$root}/outside", "{$root}/source/excluded/directory-link");

    $selection = (new FileSelection("{$root}/source"))
        ->excludeFilesFrom("{$root}/source/excluded")
        ->shouldFollowLinks($followLinks);

    $files = iterator_to_array($selection->selectedFiles(), false);

    expect($files)
        ->toContain("{$root}/source/excluded/file-link.txt", "{$root}/source/excluded/directory-link")
        ->not->toContain("{$root}/source/excluded/skip.txt");

    expect(in_array("{$root}/source/excluded/directory-link/keep.txt", $files))->toBe($followLinks);
})->with([false, true]);

it('refreshes exclusion types when selecting files again', function () {
    $this->initializeTempDirectory();
    $root = $this->getTempDirectory();
    file_put_contents("{$root}/excluded", 'file');
    file_put_contents("{$root}/excluded-sibling", 'keep');

    $selection = (new FileSelection($root))->excludeFilesFrom("{$root}/excluded");

    expect(iterator_to_array($selection->selectedFiles(), false))->not->toContain("{$root}/excluded");

    unlink("{$root}/excluded");
    mkdir("{$root}/excluded");
    file_put_contents("{$root}/excluded/skip.txt", 'skip');
    clearstatcache();

    expect(iterator_to_array($selection->selectedFiles(), false))
        ->not->toContain("{$root}/excluded", "{$root}/excluded/skip.txt")
        ->toContain("{$root}/excluded-sibling");
});

it('uses an overridden shouldExclude method', function () {
    $fileSelection = new class($this->sourceDirectory) extends FileSelection
    {
        protected function shouldExclude(string $path): bool
        {
            return basename($path) === 'directory1' || parent::shouldExclude($path);
        }
    };

    $selectedFiles = array_map(
        fn (string $path) => str_replace(DIRECTORY_SEPARATOR, '/', substr($path, strlen($this->sourceDirectory) + 1)),
        iterator_to_array($fileSelection->selectedFiles(), false),
    );

    expect($selectedFiles)
        ->not->toContain('directory1', 'directory1/directory1', 'directory2/directory1')
        ->toContain('directory1/file1.txt', 'directory1/directory1/file1.txt', 'directory2/directory1/file1.txt', 'file1.txt');
});

function assertSameArrayContent($expected, $actual, $message = '')
{
    test()->assertCount(count($expected), array_intersect($expected, $actual), $message);
}

function getTestFiles(array $relativePaths): array
{
    $testDirectory = test()->getStubDirectory();

    return array_map(fn ($path) => "{$testDirectory}/{$path}", $relativePaths);
}

function assertSameArray(array $array1, array $array2)
{
    sort($array1);
    sort($array2);
    expect($array2)->toBe($array1);
}
