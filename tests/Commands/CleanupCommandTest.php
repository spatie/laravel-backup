<?php

namespace Spatie\Backup\Tests\Commands;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Events\CleanupWasSuccessful;
use Spatie\Backup\Tests\TestCase;

class CleanupCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->setNow(2016, 1, 1, 22, 00, 00);
    }

    /** @test */
    public function it_can_remove_old_backups_until_using_less_than_maximum_storage()
    {
        config()->set('backup.cleanup.default_strategy.delete_oldest_backups_when_using_more_megabytes_than', 2);
        $this->create1MbFileOnDisk('local', 'mysite/test1.zip', now()->subDays(1));
        $this->create1MbFileOnDisk('local', 'mysite/test2.zip', now()->subDays(2));
        $this->create1MbFileOnDisk('local', 'mysite/test3.zip', now()->subDays(3));
        $this->create1MbFileOnDisk('local', 'mysite/test4.zip', now()->subDays(4));

        $this->artisan('backup:clean')->assertExitCode(0);

        Storage::disk('local')->assertExists('mysite/test1.zip');
        Storage::disk('local')->assertExists('mysite/test2.zip');
        Storage::disk('local')->assertMissing('mysite/test3.zip');
        Storage::disk('local')->assertMissing('mysite/test4.zip');
    }

    /** @test */
    public function it_can_remove_old_backups_from_the_backup_directory()
    {
        [$expectedRemainingBackups, $expectedDeletedBackups] = Collection::times(1000)
            ->flatMap(function (int $numberOfDays) {
                $date = now()->subDays($numberOfDays);

                return [
                    $this->createFileOnDisk('local', "mysite/test_{$date->format('Ymd')}_first.zip", $date),
                    $this->createFileOnDisk('local', "mysite/test_{$date->format('Ymd')}_second.zip", $date->addHour(2)),
                ];
            })->partition(function (string $backupPath) {
                return in_array($backupPath, [
                    'mysite/test_20131231_first.zip',
                    'mysite/test_20141231_first.zip',
                    'mysite/test_20150630_first.zip',
                    'mysite/test_20150731_first.zip',
                    'mysite/test_20150831_first.zip',
                    'mysite/test_20150930_first.zip',
                    'mysite/test_20151018_first.zip',
                    'mysite/test_20151025_first.zip',
                    'mysite/test_20151101_first.zip',
                    'mysite/test_20151108_first.zip',
                    'mysite/test_20151115_first.zip',
                    'mysite/test_20151122_first.zip',
                    'mysite/test_20151129_first.zip',
                    'mysite/test_20151206_first.zip',
                    'mysite/test_20151209_first.zip',
                    'mysite/test_20151210_first.zip',
                    'mysite/test_20151211_first.zip',
                    'mysite/test_20151212_first.zip',
                    'mysite/test_20151213_first.zip',
                    'mysite/test_20151214_first.zip',
                    'mysite/test_20151215_first.zip',
                    'mysite/test_20151216_first.zip',
                    'mysite/test_20151217_first.zip',
                    'mysite/test_20151218_first.zip',
                    'mysite/test_20151219_first.zip',
                    'mysite/test_20151220_first.zip',
                    'mysite/test_20151221_first.zip',
                    'mysite/test_20151222_first.zip',
                    'mysite/test_20151223_first.zip',
                    'mysite/test_20151224_first.zip',
                    'mysite/test_20151225_second.zip',
                    'mysite/test_20151225_first.zip',
                    'mysite/test_20151226_second.zip',
                    'mysite/test_20151226_first.zip',
                    'mysite/test_20151226_first.zip',
                    'mysite/test_20151227_second.zip',
                    'mysite/test_20151227_first.zip',
                    'mysite/test_20151228_second.zip',
                    'mysite/test_20151228_first.zip',
                    'mysite/test_20151229_second.zip',
                    'mysite/test_20151229_first.zip',
                    'mysite/test_20151230_second.zip',
                    'mysite/test_20151230_first.zip',
                    'mysite/test_20151231_second.zip',
                    'mysite/test_20151231_first.zip',
                    'mysite/test_20160101_second.zip',
                    'mysite/test_20160101_first.zip',
                ]);
            });

        $this->artisan('backup:clean')->assertExitCode(0);

        Storage::disk('local')
            ->assertExists($expectedRemainingBackups->toArray())
            ->assertMissing($expectedDeletedBackups->toArray());
    }

    /** @test */
    public function it_will_leave_non_zip_files_alone()
    {
        $paths = collect([
            $this->createFileOnDisk('local', 'mysite/test1.txt', now()->subDays(1)),
            $this->createFileOnDisk('local', 'mysite/test2.txt', now()->subDays(2)),
            $this->createFileOnDisk('local', 'mysite/test1000.txt', now()->subDays(1000)),
            $this->createFileOnDisk('local', 'mysite/test2000.txt', now()->subDays(2000)),
        ]);

        $this->artisan('backup:clean')->assertExitCode(0);

        $paths->each(function (string $path) {
            Storage::disk('local')->assertExists($path);
        });
    }

    /** @test */
    public function it_will_never_delete_the_newest_backup()
    {
        $backupPaths = collect(range(5, 10))->map(function (int $numberOfYears) {
            $date = now()->subYears($numberOfYears);

            return $this->createFileOnDisk('local', "mysite/test_{$date->format('Ymd')}.zip", $date);
        });

        $this->artisan('backup:clean')->assertExitCode(0);

        Storage::disk('local')->assertExists($backupPaths->first());

        $backupPaths->shift();

        $backupPaths->each(function (string $path) {
            Storage::disk('local')->assertMissing($path);
        });
    }

    /** @test */
    public function it_should_trigger_the_cleanup_successful_event()
    {
        $this->artisan('backup:clean')->assertExitCode(0);

        Event::assertDispatched(CleanupWasSuccessful::class);
    }

    /** @test */
    public function it_should_omit_the_cleanup_successful_event_when_the_notifications_are_disabled()
    {
        $this->artisan('backup:clean --disable-notifications')->assertExitCode(0);

        Event::assertNotDispatched(CleanupWasSuccessful::class);
    }

    /** @test */
    public function it_should_display_correct_used_storage_amount_after_cleanup()
    {
        config()->set('backup.cleanup.default_strategy.delete_oldest_backups_when_using_more_megabytes_than', 4);

        Collection::times(10)->each(function (int $number) {
            $this->create1MbFileOnDisk('local', "mysite/test{$number}.zip", now()->subDays($number));
        });

        Artisan::call('backup:clean');

        $this->seeInConsoleOutput('after cleanup: 4 MB.');
    }
}
