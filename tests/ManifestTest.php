<?php

use Generator;
use Spatie\Backup\Tasks\Backup\Manifest;

uses(TestCase::class);

beforeEach(function () {
    $this->initializeTempDirectory();

    $this->pathToManifest = "{$this->getTempDirectory()}/manifest.txt";

    $this->manifest = new Manifest($this->pathToManifest);
});

it('will create an empty file when it is instantiated', function () {
    $this->assertFileExists($this->pathToManifest);

    $this->assertEquals(0, filesize($this->pathToManifest));
});

it('provides a factory method', function () {
    $this->assertInstanceOf(Manifest::class, Manifest::create($this->pathToManifest));
});

it('can determine its own path', function () {
    $this->assertSame($this->manifest->path(), $this->pathToManifest);
});

it('can count the amount of files in it', function () {
    $this->assertSame(0, $this->manifest->count());
});

it('implements the countable interface', function () {
    $this->assertCount(0, $this->manifest);
});

test('a file can be added to it', function () {
    $this->manifest->addFiles($this->getStubDirectory().'/file1');

    $this->assertSame(1, $this->manifest->count());
});

test('an array of files can be added to it', function () {
    $testFiles = getTestFiles();

    $this->assertGreaterThan(0, count($testFiles));

    $this->manifest->addFiles($testFiles);

    $this->assertCount(count($testFiles), $this->manifest);
});

it('will not add an empty path', function () {
    $this->manifest->addFiles('');

    $this->assertCount(0, $this->manifest);
});

it('can return a generator to loop over all the files in the manifest', function () {
    $testFiles = getTestFiles();

    $this->manifest->addFiles($testFiles);

    $this->assertInstanceOf(Generator::class, $this->manifest->files());

    $i = 0;
    foreach ($this->manifest->files() as $filePath) {
        $this->assertSame($testFiles[$i++], $filePath);
    }
});

// Helpers
function getTestFiles(): array
{
    return collect(range(1, 3))->map(fn (int $number) => test()->getStubDirectory()."/file{$number}.txt")->toArray();
}
