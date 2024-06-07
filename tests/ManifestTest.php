<?php

use Spatie\Backup\Tasks\Backup\Manifest;

beforeEach(function () {
    $this->initializeTempDirectory();

    $this->pathToManifest = "{$this->getTempDirectory()}/manifest.txt";

    $this->manifest = new Manifest($this->pathToManifest);
});

it('will create an empty file when it is instantiated', function () {
    expect($this->pathToManifest)->toBeFile();

    expect(filesize($this->pathToManifest))->toEqual(0);
});

it('provides a factory method', function () {
    expect(Manifest::create($this->pathToManifest))->toBeInstanceOf(Manifest::class);
});

it('can determine its own path', function () {
    expect($this->pathToManifest)->toBe($this->manifest->path());
});

it('can count the amount of files in it', function () {
    expect($this->manifest->count())->toBe(0);
});

it('implements the countable interface', function () {
    expect($this->manifest)->toHaveCount(0);
});

test('a file can be added to it', function () {
    $this->manifest->addFiles($this->getStubDirectory().'/file1');

    expect($this->manifest->count())->toBe(1);
});

test('an array of files can be added to it', function () {
    $testFiles = getManifestTestFiles();

    expect(count($testFiles))->toBeGreaterThan(0);

    $this->manifest->addFiles($testFiles);

    expect($this->manifest)->toHaveCount(count($testFiles));
});

it('will not add an empty path', function () {
    $this->manifest->addFiles('');

    expect($this->manifest)->toHaveCount(0);
});

it('can return a generator to loop over all the files in the manifest', function () {
    $testFiles = getManifestTestFiles();

    $this->manifest->addFiles($testFiles);

    expect($this->manifest->files())->toBeInstanceOf(Generator::class);

    $i = 0;
    foreach ($this->manifest->files() as $filePath) {
        expect($filePath)->toBe($testFiles[$i++]);
    }
});

function getManifestTestFiles(): array
{
    return collect(range(1, 3))
        ->map(fn (int $number) => test()->getStubDirectory()."/file{$number}.txt")
        ->toArray();
}
