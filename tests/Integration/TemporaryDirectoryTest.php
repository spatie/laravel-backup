<?php

namespace Spatie\Backup\Test\Integration;

use Carbon\Carbon;
use Spatie\Backup\Tasks\Backup\TemporaryDirectory;

class TemporaryDirectoryTest extends TestCase
{
    /** @var \Carbon\Carbon */
    protected $date;

    /** @var \Spatie\Backup\Tasks\Backup\TemporaryDirectory */
    protected $temporaryDirectory;

    public function setUp()
    {
        parent::setUp();

        $this->date = Carbon::now();

        Carbon::setTestNow($this->date);

        $this->temporaryDirectory = TemporaryDirectory::create();
    }

    /** @test */
    public function it_can_determine_it_own_path()
    {
        $path = storage_path('app/laravel-backup/temp/' . $this->date->format('Y-m-d-h-i-s'));

        $this->assertEquals($path, $this->temporaryDirectory->getPath());

        $this->assertTrue(is_dir($path) && file_exists($path));
    }
}