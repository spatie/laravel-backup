<?php

use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupCollection;
use Spatie\Backup\BackupDestination\BackupDestination;

it('can count all the files', function () {
    createFileOnBackupDisk('file1.zip');
    createFileOnBackupDisk('file2.zip');

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    expect($backupCollection)->toHaveCount(2);
});

it('will not take into account non zip files', function () {
    createFileOnBackupDisk('file1.txt');
    createFileOnBackupDisk('file2.zip');

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    expect($backupCollection)->toHaveCount(1);
});

it('can handle empty disks', function () {
    $backupCollection = getBackupCollectionForCurrentDiskContents();

    expect($backupCollection)->toHaveCount(0);
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

    expect($backupCollection->first())->toBeInstanceOf(Backup::class);
});

it('can determine the newest backup', function () {
    createFileOnBackupDisk('file1.zip', 3);
    createFileOnBackupDisk('file2.zip', 1);
    createFileOnBackupDisk('file3.zip', 2);

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    expect($backupCollection->newest()->path())->toBe('mysite.com/file2.zip');
});

it('can determine the oldest backup', function () {
    createFileOnBackupDisk('file1.zip', 3);
    createFileOnBackupDisk('file2.zip', 1);
    createFileOnBackupDisk('file3.zip', 2);

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    expect($backupCollection->oldest()->path())->toBe('mysite.com/file1.zip');
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

    expect($backupCollection->size())->toBeGreaterThan(0);

    expect($backupCollection->size())->toBe(floatval($totalSize));
});

it('need a float type size', function () {
    createFileOnBackupDisk('file1.zip', 3);
    createFileOnBackupDisk('file2.zip', 1);
    createFileOnBackupDisk('file3.zip', 2);

    $backupCollection = getBackupCollectionForCurrentDiskContents();

    expect($backupCollection->size())->toBeFloat();
});

it('takes the backup sizes from the disk listing', function () {
    $path = createFileOnBackupDisk('file1.zip');

    $size = Storage::disk('local')->size($path);

    $backups = BackupDestination::create('local', 'mysite.com')->backups();

    Storage::disk('local')->delete($path);

    expect($backups->first()->exists())->toBeTrue();
    expect($backups->size())->toBe(floatval($size));
});

it('respects disks that customise how files are listed', function () {
    createFileOnBackupDisk('file1.zip');
    createFileOnBackupDisk('file2.zip');

    $local = Storage::disk('local');

    $disk = new class($local->getDriver(), $local->getAdapter(), []) extends FilesystemAdapter
    {
        public function allFiles($directory = null)
        {
            return ['mysite.com/file1.zip'];
        }
    };

    $backups = (new BackupDestination($disk, 'mysite.com', 'local'))->backups();

    expect($backups->map->path()->all())->toBe(['mysite.com/file1.zip']);
});

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
