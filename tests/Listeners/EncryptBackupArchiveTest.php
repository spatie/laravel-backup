<?php

use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Listeners\EncryptBackupArchive;

it('still encrypts an archive when invoked manually', function () {
    $this->initializeTempDirectory();

    config()->set('backup.backup.password', '24dsjF6BPjWgUfTu');

    $path = $this->getTempDirectory().'/archive.zip';

    copy(__DIR__.'/../stubs/archive.zip', $path);

    app(EncryptBackupArchive::class)->handle(new BackupZipWasCreated($path));

    $zip = new ZipArchive;
    $zip->open($path);

    foreach (range(0, $zip->numFiles - 1) as $i) {
        expect($zip->statIndex($i)['encryption_method'])->toBe(ZipArchive::EM_AES_256);
    }

    $zip->setPassword('24dsjF6BPjWgUfTu');
    expect($zip->extractTo($this->getTempDirectory().'/extraction'))->toBeTrue();

    foreach (['file1.txt', 'file2.txt', 'file3.txt'] as $filename) {
        expect(file_get_contents($this->getTempDirectory().'/extraction/'.$filename))->toBe('lorum ipsum');
    }

    $zip->close();
});
