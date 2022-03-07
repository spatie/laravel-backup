<?php

use Spatie\Backup\Tasks\Backup\Zip;

uses(TestCase::class);

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
