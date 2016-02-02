<?php

namespace Spatie\Backup\Test\Integration;

use Illuminate\Support\Facades\Artisan;

class BackupJobTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_can_run_backup_job()
    {
        Artisan::call('backup:run', ['--only-files' => true]);

        $this->fileWithExtensionExistsInDirectoryOnDisk('zip', 'backups', 'local');
    }
}
