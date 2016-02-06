<?php

namespace Spatie\Backup\Test\Integration;

use Illuminate\Support\Facades\Artisan;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class Tinker extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /** @test */
    public function it_tests()
    {
        Artisan::call('backup:run', ['--only-files' => true]);

        $dest = BackupDestinationFactory::createFromArray(config('laravel-backup.backup'));

        dd(
            collect(['a' => 1, 'b' => 3])->map(function ($test) {
                return 'bla'.$test;
            })
    );

        dd($dest[0]->getBackups()[0]->getLastModifiedDate());
    }
}
