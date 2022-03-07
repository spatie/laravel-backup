<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupCollection;
use Spatie\Backup\Tests\TestCase;

uses(TestCase::class);

it('can count all the files', function () {
    createFileOnBackupDisk('file1.zip');
    createFileOnBackupDisk('file2.zip');

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    $this->assertCount(2, $backupCollection);
});

it('will not take into account non zip files', function () {
    createFileOnBackupDisk('file1.txt');
    createFileOnBackupDisk('file2.zip');

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    $this->assertCount(1, $backupCollection);
});

it('can handle empty disks', function () {
    $backupCollection = getBackupCollectionForCurrentDiskContents();

    $this->assertCount(0, $backupCollection);
});

it('can return all files in order of descending age', function () {
    createFileOnBackupDisk('file1.zip', 3);
    createFileOnBackupDisk('file2.zip', 4);
    createFileOnBackupDisk('file3.zip', 2);
    createFileOnBackupDisk('file4.zip', 5);
    createFileOnBackupDisk('file5.zip', 1);

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    $filePaths = $backupCollection->map(function (Backup $backup) {
        return $backup->path();
    })->toArray();

    $this->assertSame([
        'mysite.com/file5.zip',
        'mysite.com/file3.zip',
        'mysite.com/file1.zip',
        'mysite.com/file2.zip',
        'mysite.com/file4.zip',
    ], $filePaths);
});

it('will hold backup objects', function () {
    createFileOnBackupDisk('file1.zip');

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    $this->assertInstanceOf(Backup::class, $backupCollection->first());
});

it('can determine the newest backup', function () {
    createFileOnBackupDisk('file1.zip', 3);
    createFileOnBackupDisk('file2.zip', 1);
    createFileOnBackupDisk('file3.zip', 2);

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    $this->assertSame('mysite.com/file2.zip', $backupCollection->newest()->path());
});

it('can determine the oldest backup', function () {
    createFileOnBackupDisk('file1.zip', 3);
    createFileOnBackupDisk('file2.zip', 1);
    createFileOnBackupDisk('file3.zip', 2);

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    $this->assertSame('mysite.com/file1.zip', $backupCollection->oldest()->path());
});

it('can determine the size of the backups', function () {
    $paths = collect([
        createFileOnBackupDisk('file1.zip', 1),
        createFileOnBackupDisk('file2.zip', 1),
        createFileOnBackupDisk('file3.zip', 1),
    ]);

    $totalSize = $paths->sum(function (string $path) {
        return Storage::disk('local')->size($path);
    });

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    $this->assertGreaterThan(0, $backupCollection->size());

    $this->assertSame(floatval($totalSize), $backupCollection->size());
});

it('need a float type size', function () {
    createFileOnBackupDisk('file1.zip', 3);
    createFileOnBackupDisk('file2.zip', 1);
    createFileOnBackupDisk('file3.zip', 2);

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    $this->assertIsFloat($backupCollection->size());
});

// Helpers
function getBackupCollectionForCurrentDiskContents(): BackupCollection
{
    $disk = Storage::disk('local');

    $files = $disk->allFiles('mysite.com');

    return BackupCollection::createFromFiles($disk, $files);
}

function createFileOnBackupDisk(string $name, int $ageInDays = 0): string
{
    return test()->createFileOnDisk(
        'local',
        'mysite.com/'.$name,
        Carbon::now()->subDays($ageInDays)
    );
}
