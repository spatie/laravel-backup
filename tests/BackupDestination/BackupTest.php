<?php

namespace Spatie\Backup\Tests\BackupDestination;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Spatie\Backup\Tests\TestCase;

class BackupTest extends TestCase
{
    /** @test */
    public function it_can_determine_the_disk_of_the_backup()
    {
        $fileName = 'test.zip';

        $backup = $this->getBackupForFile($fileName);

        $this->assertSame(Storage::disk('local'), $backup->disk());
    }

    /** @test */
    public function it_can_determine_the_path_of_the_backup()
    {
        $fileName = 'test.zip';

        $backup = $this->getBackupForFile($fileName);

        $this->assertSame("mysite.com/{$fileName}", $backup->path());
    }

    /** @test */
    public function it_can_delete_itself()
    {
        $fileName = 'test.zip';

        $backup = $this->getBackupForFile($fileName);

        $this->assertTrue($backup->exists());

        Storage::disk('local')->assertExists('mysite.com/test.zip');

        $backup->delete();

        $this->assertFalse($backup->exists());

        Storage::disk('local')->assertMissing('mysite.com/test.zip');
    }

    /** @test */
    public function it_can_determine_its_size()
    {
        $backup = $this->getBackupForFile('test.zip', 0, 'this backup has content');

        $fileSize = floatval(Storage::disk('local')->size('mysite.com/test.zip'));

        $this->assertSame($fileSize, $backup->size());

        $this->assertGreaterThan(0, $backup->size());
    }

    /** @test */
    public function it_can_determine_its_size_even_after_it_has_been_deleted()
    {
        $backup = $this->getBackupForFile('test.zip', 0, 'this backup has content');

        $backup->delete();

        $this->assertSame(0.0, $backup->size());
    }

    /** @test */
    public function it_push_backup_extra_option_to_write_stream_if_set()
    {
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
    }

    /** @test */
    public function it_push_empty_default_backup_extra_option_to_write_stream_if_not_set()
    {
        config()->set('filesystems.disks.s3-test-backup', [
            'driver' => 'local',

        ]);

        config()->set('backup.backup.destination.disks', [
            'local',
        ]);

        $backupDestination = BackupDestinationFactory::createFromArray(config('backup.backup'))->first();

        $this->assertSame([], $backupDestination->getDiskOptions());
    }

    /** @test */
    public function it_need_a_float_type_size()
    {
        $backup = $this->getBackupForFile('test.zip', 0, 'this backup has content');

        $this->assertIsFloat($backup->size());
    }

    protected function getBackupForFile(string $name, int $ageInDays = 0): Backup
    {
        $disk = Storage::disk('local');

        $path = 'mysite.com/'.$name;

        $this->createFileOnDisk('local', $path, Carbon::now()->subDays($ageInDays));

        return new Backup($disk, $path);
    }
}
