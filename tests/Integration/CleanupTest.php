<?php

namespace Spatie\Backup\Test\Integration;

use Illuminate\Support\Facades\Artisan;

class CleanupTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->testHelper->initializeTempDirectory();

        $app = $this->app;

        $app['config']->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => $this->testHelper->getTempDirectory(),
        ]);

    }

    /** @test */
    public function it_can_remove_old_files_from_the_backup_directory()
    {
        Artisan::call('backup:clean');
    }
}
