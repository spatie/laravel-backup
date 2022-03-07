<?php

use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Mockery as m;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Exceptions\InvalidBackupFile;
use Spatie\Backup\Tests\TestCase;

uses(TestCase::class);

it('can determine the disk of the backup', function () {
    $fileName = 'test.zip';

    $backup = getBackupForFile($fileName);

    $this->assertSame(Storage::disk('local'), $backup->disk());
});

it('can determine the path of the backup', function () {
    $fileName = 'test.zip';

    $backup = getBackupForFile($fileName);

    $this->assertSame("mysite.com/{$fileName}", $backup->path());
});

it('can get backup as stream resource', function () {
    $fileName = 'test.zip';

    $backup = getBackupForFile($fileName);

    $this->assertIsResource($backup->stream());
});

test('when its unable to read the stream throws exception', function () {
    $path = 'mysite.com/test.zip';

    $filesystem = m::mock(FilesystemAdapter::class);
    $filesystem->shouldReceive('readStream')->once()->with($path)->andReturn(false);

    $backup = new Backup($filesystem, $path);

    $this->expectException(InvalidBackupFile::class);
    $backup->stream();
});

it('can delete itself', function () {
    $fileName = 'test.zip';

    $backup = getBackupForFile($fileName);

    $this->assertTrue($backup->exists());

    Storage::disk('local')->assertExists('mysite.com/test.zip');

    $backup->delete();

    $this->assertFalse($backup->exists());

    Storage::disk('local')->assertMissing('mysite.com/test.zip');
});

it('can determine its size', function () {
    $backup = getBackupForFile('test.zip', 0, 'this backup has content');

    $fileSize = floatval(Storage::disk('local')->size('mysite.com/test.zip'));

    $this->assertSame($fileSize, $backup->sizeInBytes());

    $this->assertGreaterThan(0, $backup->sizeInBytes());
});

it('can determine its size even after it has been deleted', function () {
    $backup = getBackupForFile('test.zip', 0, 'this backup has content');

    $backup->delete();

    $this->assertSame(0.0, $backup->sizeInBytes());
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

    $this->assertEquals(['StorageClass' => 'COLD'], $backupDestination->getDiskOptions());
});

it('push empty default backup extra option to write stream if not set', function () {
    config()->set('filesystems.disks.s3-test-backup', [
        'driver' => 'local',

    ]);

    config()->set('backup.backup.destination.disks', [
        'local',
    ]);

    $backupDestination = BackupDestinationFactory::createFromArray(config('backup.backup'))->first();

    $this->assertSame([], $backupDestination->getDiskOptions());
});

it('need a float type size', function () {
    $backup = getBackupForFile('test.zip', 0, 'this backup has content');

    $this->assertIsFloat($backup->sizeInBytes());
});

// Helpers
function getBackupForFile(string $name, int $ageInDays = 0): Backup
{
    $disk = Storage::disk('local');

    $path = 'mysite.com/'.$name;

    test()->createFileOnDisk('local', $path, Carbon::now()->subDays($ageInDays));

    return new Backup($disk, $path);
}
