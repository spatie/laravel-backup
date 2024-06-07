<?php

use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mockery as m;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Exceptions\InvalidBackupFile;

it('can determine the disk of the backup', function () {
    $fileName = 'test.zip';

    $backup = getBackupForFile($fileName);

    expect($backup->disk())->toBe(Storage::disk('local'));
});

it('can determine the path of the backup', function () {
    $fileName = 'test.zip';

    $backup = getBackupForFile($fileName);

    expect($backup->path())->toBe("mysite.com/{$fileName}");
});

it('can get backup as stream resource', function () {
    $fileName = 'test.zip';

    $backup = getBackupForFile($fileName);

    expect($backup->stream())->toBeResource();
});

test('when its unable to read the stream throws exception', function () {
    $path = 'mysite.com/test.zip';

    $filesystem = m::mock(FilesystemAdapter::class);
    $filesystem->shouldReceive('exists')->once()->with($path)->andReturn(true);
    $filesystem->shouldReceive('readStream')->once()->with($path)->andReturn(false);

    $backup = new Backup($filesystem, $path);

    $this->expectException(InvalidBackupFile::class);
    $backup->stream();
});

it('can delete itself', function () {
    $fileName = 'test.zip';

    $backup = getBackupForFile($fileName);

    expect($backup->exists())->toBeTrue();

    Storage::disk('local')->assertExists('mysite.com/test.zip');

    $backup->delete();

    expect($backup->exists())->toBeFalse();

    Storage::disk('local')->assertMissing('mysite.com/test.zip');
});

it('can determine its size', function () {
    $backup = getBackupForFile('test.zip', 0);

    $fileSize = floatval(Storage::disk('local')->size('mysite.com/test.zip'));

    expect($backup->sizeInBytes())->toBe($fileSize);

    expect($backup->sizeInBytes())->toBeGreaterThan(0);
});

it('can determine its size even after it has been deleted', function () {
    $backup = getBackupForFile('test.zip', 0);

    $backup->delete();

    expect($backup->sizeInBytes())->toBe(0.0);
});

it('push backup extra option to write stream if set', function () {
    config()->set('filesystems.disks.s3-test-backup', [
        'driver' => 's3',

        'backup_options' => [
            'StorageClass' => 'COLD',
        ],
    ]);

    config()->set('backup.backup.destination.disks', [
        's3-test-backup',
    ]);

    $backupDestination = BackupDestinationFactory::createFromArray(config('backup.backup'))->first();

    expect($backupDestination->getDiskOptions())->toEqual(['StorageClass' => 'COLD']);
});

it('push empty default backup extra option to write stream if not set', function () {
    config()->set('filesystems.disks.s3-test-backup', [
        'driver' => 'local',

    ]);

    config()->set('backup.backup.destination.disks', [
        'local',
    ]);

    $backupDestination = BackupDestinationFactory::createFromArray(config('backup.backup'))->first();

    expect($backupDestination->getDiskOptions())->toBe([]);
});

it('need a float type size', function () {
    $backup = getBackupForFile('test.zip', 0);

    expect($backup->sizeInBytes())->toBeFloat();
});

function getBackupForFile(string $name, int $ageInDays = 0): Backup
{
    $disk = Storage::disk('local');

    $path = 'mysite.com/'.$name;

    test()->createFileOnDisk('local', $path, Carbon::now()->subDays($ageInDays));

    return new Backup($disk, $path);
}
