<?php

namespace Spatie\Backup\Test\Integration\BackupCollectionTest;

use Storage;
use Exception;
use Carbon\Carbon;
use League\Flysystem\Filesystem;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Test\Integration\TestCase;
use League\Flysystem\Adapter\Local as LocalAdapter;
use Spatie\Backup\BackupDestination\BackupCollection;

class BackupCollectionTest extends TestCase
{
    /** @test */
    public function it_can_count_all_the_files()
    {
        $this->createFileOnBackupDisk('file1.zip');
        $this->createFileOnBackupDisk('file2.zip');

        $backupCollection = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertCount(2, $backupCollection);
    }

    /** @test */
    public function it_will_not_take_into_account_non_zip_files()
    {
        $this->createFileOnBackupDisk('file1.txt');
        $this->createFileOnBackupDisk('file2.zip');

        $backupCollection = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertCount(1, $backupCollection);
    }

    /** @test */
    public function it_can_handle_empty_disks()
    {
        $backupCollection = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertCount(0, $backupCollection);
    }

    /** @test */
    public function it_can_return_all_files_in_order_of_descending_age()
    {
        $this->createFileOnBackupDisk('file1.zip', 3);
        $this->createFileOnBackupDisk('file2.zip', 4);
        $this->createFileOnBackupDisk('file3.zip', 2);
        $this->createFileOnBackupDisk('file4.zip', 5);
        $this->createFileOnBackupDisk('file5.zip', 1);

        $backupCollection = $this->getBackupCollectionForCurrentDiskContents();

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
    }

    /** @test */
    public function it_will_hold_backup_objects()
    {
        $this->createFileOnBackupDisk('file1.zip');

        $backupCollection = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertInstanceOf(Backup::class, $backupCollection->first());
    }

    /** @test */
    public function it_can_determine_the_newest_backup()
    {
        $this->createFileOnBackupDisk('file1.zip', 3);
        $this->createFileOnBackupDisk('file2.zip', 1);
        $this->createFileOnBackupDisk('file3.zip', 2);

        $backupCollection = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertSame('mysite.com/file2.zip', $backupCollection->newest()->path());
    }

    /** @test */
    public function it_can_determine_the_oldest_backup()
    {
        $this->createFileOnBackupDisk('file1.zip', 3);
        $this->createFileOnBackupDisk('file2.zip', 1);
        $this->createFileOnBackupDisk('file3.zip', 2);

        $backupCollection = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertSame('mysite.com/file1.zip', $backupCollection->oldest()->path());
    }

    /** @test */
    public function it_can_determine_the_size_of_the_backups()
    {
        $this->createFileOnBackupDisk('file1.zip', 1, gzencode('some content'));
        $this->createFileOnBackupDisk('file2.zip', 1, gzencode('even more content'));
        $this->createFileOnBackupDisk('file3.zip', 1, gzencode('you guessed it: content'));

        $totalSize = filesize($this->testHelper->getTempDirectory().'/mysite.com/file1.zip')
            + filesize($this->testHelper->getTempDirectory().'/mysite.com/file2.zip')
            + filesize($this->testHelper->getTempDirectory().'/mysite.com/file3.zip');

        $backupCollection = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertGreaterThan(0, $backupCollection->size());

        $this->assertSame($totalSize, $backupCollection->size());
    }

    /** @test */
    public function it_checks_mime_type_instead_of_extension()
    {
        $this->localFilesystemOnMimeTypeCheckToReturn(['mimetype' => 'application/zip']);
        $this->createFileOnBackupDisk('file1');

        $backups = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertCount(1, $backups);
    }

    /** @test */
    public function it_skips_mime_type_check_if_mime_type_is_false()
    {
        $this->localFilesystemOnMimeTypeCheckToReturn(false);
        $this->createFileOnBackupDisk('file1.zip');

        $backups = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertCount(1, $backups);
    }

    /** @test */
    public function it_skips_mime_type_check_if_getting_mime_type_throws_exception()
    {
        $this->localFilesystemOnMimeTypeCheckToReturn(function () {
            throw new Exception('No mime type specified');
        });
        $this->createFileOnBackupDisk('file1.zip');

        $backups = $this->getBackupCollectionForCurrentDiskContents();

        $this->assertCount(1, $backups);
    }

    protected function getBackupCollectionForCurrentDiskContents(): BackupCollection
    {
        $disk = Storage::disk('local');

        $files = $disk->allFiles('mysite.com');

        return BackupCollection::createFromFiles($disk, $files);
    }

    protected function createFileOnBackupDisk(string $name, int $ageInDays = 0, string $contents = '')
    {
        $this->testHelper->createTempFileWithAge(
            'mysite.com/'.$name,
            Carbon::now()->subDays($ageInDays),
            $contents
        );
    }

    protected function localFilesystemOnMimeTypeCheckToReturn($mimeType)
    {
        LocalAdapterWithMimeType::$mimeType = $mimeType;

        Storage::extend('local', function ($app, $config) {
            return new Filesystem(new LocalAdapterWithMimeType($config['root']));
        });
    }
}

class LocalAdapterWithMimeType extends LocalAdapter
{
    /** @var array|false|callable */
    public static $mimeType;

    /**
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        return is_callable(static::$mimeType) ? (static::$mimeType)() : static::$mimeType;
    }
}
