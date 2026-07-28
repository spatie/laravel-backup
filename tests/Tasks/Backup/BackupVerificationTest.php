<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Exceptions\BackupVerificationFailed;

beforeEach(function () {
    $this->setNow(2016, 1, 1, 21, 1, 1);

    $this->expectedZipPath = 'mysite/2016-01-01-21-01-01.zip';

    config()->set('backup.backup.destination.disks', ['local']);
    config()->set('backup.backup.source.files.include', [$this->getStubDirectory()]);
    config()->set('backup.backup.source.files.exclude', []);
    config()->set('backup.backup.verify_backup', true);
});

it('completes the backup when the archive contains every file of the manifest', function () {
    config()->set('backup.backup.source.databases', ['db1', 'db2']);

    $this->artisan('backup:run')->assertExitCode(0);

    Storage::disk('local')->assertExists($this->expectedZipPath);
});

it('fails the backup when the archive contains fewer files than the manifest', function () {
    removeFirstFileFromCreatedZip();

    $failure = null;
    Event::listen(BackupHasFailed::class, function (BackupHasFailed $event) use (&$failure) {
        $failure = $event->exception;
    });

    $this->artisan('backup:run --only-files')
        ->expectsOutputToContain('Backup verification failed')
        ->assertExitCode(1);

    expect($failure)->toBeInstanceOf(BackupVerificationFailed::class);

    Storage::disk('local')->assertMissing($this->expectedZipPath);
});

it('does not verify the archive when verification is disabled', function () {
    config()->set('backup.backup.verify_backup', false);

    removeFirstFileFromCreatedZip();

    $this->artisan('backup:run --only-files')->assertExitCode(0);

    Storage::disk('local')->assertExists($this->expectedZipPath);
});

function removeFirstFileFromCreatedZip(): void
{
    Event::listen(BackupZipWasCreated::class, function (BackupZipWasCreated $event) {
        $zip = new ZipArchive;

        $zip->open($event->pathToZip);
        $zip->deleteIndex(0);
        $zip->close();
    });
}
