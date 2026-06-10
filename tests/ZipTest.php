<?php

use Spatie\Backup\Config\Config;
use Spatie\Backup\Tasks\Backup\Zip;

beforeEach(function () {
    $this->initializeTempDirectory();

    $this->pathToZip = "{$this->getTempDirectory()}/test.zip";

    $this->zip = new Zip($this->pathToZip);
});

it('can create a zip file', function () {
    $this->zip->add(__FILE__);
    $this->zip->close();

    expect($this->pathToZip)->toBeFile();
});

it('can report its own size', function () {
    expect($this->zip->size())->toEqual(0);

    $this->zip->add(__FILE__);
    $this->zip->close();

    $this->assertNotEquals(0, $this->zip->size());
});

it('encrypts files added without a name in zip when a password is configured', function () {
    config()->set('backup.backup.password', '24dsjF6BPjWgUfTu');
    app()->forgetInstance(Config::class);

    $pathToZip = "{$this->getTempDirectory()}/encrypted-test.zip";

    $zip = new Zip($pathToZip);
    $zip->add(__FILE__);
    $zip->close();

    $zipArchive = new ZipArchive;
    $zipArchive->open($pathToZip);

    expect($zipArchive->statIndex(0)['encryption_method'])->toBe(ZipArchive::EM_AES_256);

    $zipArchive->close();
});
