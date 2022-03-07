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

    $this->assertFileExists($this->pathToZip);
});

it('can report its own size', function () {
    $this->assertEquals(0, $this->zip->size());

    $this->zip->add(__FILE__);
    $this->zip->close();

    $this->assertNotEquals(0, $this->zip->size());
});
