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

    /** @var string */
    protected $expectedDirectory;

    public function setUp()
    {
        parent::setUp();

        $this->date = Carbon::create(2016, 01, 01, 21, 01, 01);

        Carbon::setTestNow($this->date);

        $this->expectedDirectory = storage_path('app/laravel-backup/temp/'.$this->date->format('Y-m-d-H-i-s'));

        $this->temporaryDirectory = TemporaryDirectory::create();
    }

    /** @test */
    public function it_can_determine_it_own_path()
    {
        $this->assertEquals($this->expectedDirectory, $this->temporaryDirectory->path());

        $this->assertDirectoryExists($this->expectedDirectory);
    }

    /** @test */
    public function it_can_delete_itself()
    {
        $this->assertDirectoryExists($this->expectedDirectory);

        $this->temporaryDirectory->delete();

        $this->assertDirectoryNotExists($this->expectedDirectory);
    }

    /** @test */
    public function it_can_delete_it_self_even_if_the_directory_is_not_empty()
    {
        copy($this->testHelper->getStubDirectory().'/file1.txt', $this->temporaryDirectory->path().'/file1.txt');

        $this->temporaryDirectory->delete();

        $this->assertDirectoryNotExists($this->expectedDirectory);
    }

    /** @test */
    public function it_will_create_a_subdirectory_if_the_given_path_is_likely_to_be_directory_name()
    {
        $subDirectoryName = 'subdir';

        $path = $this->temporaryDirectory->path($subDirectoryName);

        $this->assertEquals($this->expectedDirectory.'/'.$subDirectoryName, $path);

        $this->assertDirectoryExists($this->expectedDirectory.'/'.$subDirectoryName);
    }

    /** @test */
    public function it_will_not_create_a_subdirectory_if_the_given_path_is_likely_to_be_file_name()
    {
        $fileName = 'test.txt';

        $path = $this->temporaryDirectory->path($fileName);

        $this->assertEquals($this->expectedDirectory.'/'.$fileName, $path);

        $this->assertDirectoryNotExists($this->expectedDirectory.'/'.$fileName);
    }
}
