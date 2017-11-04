<?php

namespace Spatie\Backup\Test\Integration;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\Exceptions\BackupsDisabled;

class DisableBackupsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('backup.backup.run', false);
    }

    /** @test */
    public function it_can_disable_backups()
    {
        $this->expectException(BackupsDisabled::class);

        Artisan::call('backup:list');
    }
}
