<?php

namespace Spatie\Backup\Test\Integration\BackupDestination;

use Storage;
use Carbon\Carbon;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Test\Integration\TestCase;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class BackupTest extends TestCase
{
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

        $fullPath = $this->testHelper->getTempDirectory().'/'.$backup->path();

        $this->assertTrue($backup->exists());

        $this->assertFileExists($fullPath);

        $backup->delete();

        $this->assertFalse($backup->exists());

        $this->assertFileNotExists($fullPath);
    }

    /** @test */
    public function it_can_determine_its_size()
    {
        $backup = $this->getBackupForFile('test.zip', 0, 'this backup has content');

        $fileSize = filesize($this->testHelper->getTempDirectory().'/'.$backup->path());

        $this->assertSame($fileSize, $backup->size());

        $this->assertGreaterThan(0, $backup->size());
    }

    /** @test */
    public function it_can_determine_its_size_even_after_it_has_been_deleted()
    {
        $backup = $this->getBackupForFile('test.zip', 0, 'this backup has content');

        $backup->delete();

        $this->assertSame(0, $backup->size());
    }

    /** @test */
    public function it_push_backup_extra_option_to_write_stream_if_set()
    {
        $this->app['config']->set('filesystems.disks.s3-test-backup', [
            'driver' => 's3',

            'backup_options' => [
                'StorageClass' => 'COLD',
            ],
        ]);

        $this->app['config']->set('backup.backup.destination.disks', [
            's3-test-backup',
        ]);

        $backupDestination = BackupDestinationFactory::createFromArray(config('backup.backup'))->first();

        $this->assertEquals(['StorageClass' => 'COLD'], $backupDestination->extraOptions());
    }

    /** @test */
    public function it_push_empty_default_backup_extra_option_to_write_stream_if_not_set()
    {
        $this->app['config']->set('filesystems.disks.s3-test-backup', [
            'driver' => 'local',

        ]);

        $this->app['config']->set('backup.backup.destination.disks', [
            'local',
        ]);

        $backupDestination = BackupDestinationFactory::createFromArray(config('backup.backup'))->first();

        $this->assertSame([], $backupDestination->extraOptions());
    }

    protected function getBackupForFile(string $name, int $ageInDays = 0, string $contents = ''): Backup
    {
        $disk = Storage::disk('local');

        $path = 'mysite.com/'.$name;

        $this->testHelper->createTempFileWithAge(
            $path,
            Carbon::now()->subDays($ageInDays),
            $contents
        );

        return new Backup($disk, $path);
    }
}
