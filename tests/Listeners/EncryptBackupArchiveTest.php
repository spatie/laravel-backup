<?php

namespace Spatie\Backup\Tests\Listeners;

use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Tests\TestCase;
use ZipArchive;

class EncryptBackupArchiveTest extends TestCase
{
    protected const PASSWORD = '24dsjF6BPjWgUfTu';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('backup.backup.password', self::PASSWORD);
    }

    /** @test */
    public function it_keeps_archive_unencrypted_without_password(): void
    {
        config()->set('backup.backup.password', null);

        $path = $this->zip();

        $zip = new ZipArchive();
        $zip->open($path);

        $this->assertEncryptionMethod($zip, ZipArchive::EM_NONE);

        $this->assertTrue($zip->extractTo(__DIR__.'/../temp/extraction'));
        $this->assertValidExtractedFiles();

        $zip->close();
    }

    /**
     * @test
     * @dataProvider encryptionMethods
     * @param int $algorithm
     */
    public function it_encrypts_archive_with_password(int $algorithm): void
    {
        config()->set('backup.backup.encryption', $algorithm);

        $path = $this->zip();

        $zip = new ZipArchive();
        $zip->open($path);

        $this->assertEncryptionMethod($zip, $algorithm);

        $zip->setPassword(self::PASSWORD);
        $this->assertTrue($zip->extractTo(__DIR__.'/../temp/extraction'));
        $this->assertValidExtractedFiles();

        $zip->close();
    }

    /** @test */
    public function it_can_not_open_encrypted_archive_without_password(): void
    {
        $path = $this->zip();

        $zip = new ZipArchive();
        $zip->open($path);

        $this->assertEncryptionMethod($zip, ZipArchive::EM_AES_256);

        $this->assertFalse($zip->extractTo(__DIR__.'/../temp/extraction'));

        $zip->close();
    }

    protected function zip(): string
    {
        $source = __DIR__.'/../stubs/archive.zip';
        $target = __DIR__.'/../temp/archive.zip';

        copy($source, $target);

        app()->call('\Spatie\Backup\Listeners\EncryptBackupArchive@handle', ['event' => new BackupZipWasCreated($target)]);

        return $target;
    }

    public function assertEncryptionMethod(ZipArchive $zip, int $algorithm): void
    {
        foreach (range(0, $zip->numFiles - 1) as $i) {
            $this->assertSame($algorithm, $zip->statIndex($i)['encryption_method']);
        }
    }

    public function assertValidExtractedFiles(): void
    {
        foreach(['file1.txt', 'file2.txt', 'file3.txt'] as $filename) {
            $filepath = __DIR__.'/../temp/extraction/'.$filename;
            $this->assertTrue(file_exists($filepath));
            $this->assertSame('lorum ipsum', file_get_contents($filepath));
        }
    }

    public function encryptionMethods(): array
    {
        return [
            [ZipArchive::EM_AES_128],
            [ZipArchive::EM_AES_192],
            [ZipArchive::EM_AES_256],
        ];
    }
}
