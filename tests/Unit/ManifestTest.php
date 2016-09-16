<?php

namespace Spatie\Backup\Test\Unit;

use Generator;
use Spatie\Backup\Tasks\Backup\Manifest;
use Spatie\Backup\Test\TestHelper;

class ManifestTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Spatie\Backup\Test\TestHelper */
    protected $testHelper;

    /** @var string */
    protected $pathToManifest;

    /** @var \Spatie\Backup\Tasks\Backup\Manifest */
    protected $manifest;

    public function setUp()
    {
        parent::setUp();

        $this->testHelper = new TestHelper();

        $this->testHelper->initializeTempDirectory();

        $this->pathToManifest = "{$this->testHelper->getTempDirectory()}/manifest.txt";

        $this->manifest = new Manifest($this->pathToManifest);
    }

    /** @test */
    public function it_will_create_an_empty_file_when_it_is_instantiated()
    {
        $this->assertFileExists($this->pathToManifest);

        $this->assertEquals(0, filesize($this->pathToManifest));
    }

    /** @test */
    public function it_provides_a_factory_method()
    {
        $this->assertInstanceOf(Manifest::class, Manifest::create($this->pathToManifest));
    }

    /** @test */
    public function it_can_determine_its_own_path()
    {
        $this->assertSame($this->manifest->path(), $this->pathToManifest);
    }

    /** @test */
    public function it_can_count_the_amount_of_files_in_it()
    {
        $this->assertSame(0, $this->manifest->count());
    }

    /** @test */
    public function it_implements_the_countable_interface()
    {
        $this->assertSame(0, count($this->manifest));
    }

    /** @test */
    public function a_file_can_be_added_to_it()
    {
        $this->manifest->addFiles($this->testHelper->getStubDirectory().'/file1');

        $this->assertSame(1, $this->manifest->count());
    }

    /** @test */
    public function an_array_of_files_can_be_added_to_it()
    {
        $testFiles = $this->getTestFiles();

        $this->assertGreaterThan(0, count($testFiles));

        $this->manifest->addFiles($testFiles);

        $this->assertSame(count($testFiles), count($this->manifest));
    }

    /** @test */
    public function it_will_not_add_an_empty_path()
    {
        $this->manifest->addFiles('');

        $this->assertCount(0, $this->manifest);
    }

    /** @test */
    public function it_can_return_a_generator_to_loop_over_all_the_files_in_the_manifest()
    {
        $testFiles = $this->getTestFiles();

        $this->manifest->addFiles($testFiles);

        $this->assertInstanceOf(Generator::class, $this->manifest->files());

        $i = 0;
        foreach ($this->manifest->files() as $filePath) {
            $this->assertSame($testFiles[$i++], $filePath);
        }
    }

    protected function getTestFiles(): array
    {
        return collect(range(1, 3))->map(function (int $number) {
            return $this->testHelper->getStubDirectory()."/file{$number}.txt";
        })->toArray();
    }
}
