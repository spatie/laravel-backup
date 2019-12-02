<?php

namespace Spatie\Backup\Tests\Events;

use Exception;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Exceptions\InvalidHealthCheck;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound as UnhealthyBackupWasFoundNotification;
use Spatie\Backup\Tasks\Monitor\HealthCheck;
use Spatie\Backup\Tests\TestCase;

class UnhealthyBackupWasFoundTest extends TestCase
{
    /** @var \Carbon\Carbon */
    protected $date;

    /** @test */
    public function it_will_fire_an_event_on_failed_health_check()
    {
        Event::fake();

        $this
            ->fakeBackup()
            ->makeHealthCheckFail()
            ->artisan('backup:monitor')
            ->assertExitCode(1);

        Event::assertDispatched(UnhealthyBackupWasFound::class);
    }

    /** @test **/
    public function it_sends_an_notification_containing_the_exception_message_for_handled_health_check_errors()
    {
        Notification::fake();

        $this
            ->fakeBackup()
            ->makeHealthCheckFail(new InvalidHealthCheck($msg = 'This is the failure reason sent to the user'))
            ->artisan('backup:monitor')->assertExitCode(1);

        Notification::assertSentTo(new Notifiable(), UnhealthyBackupWasFoundNotification::class, function (UnhealthyBackupWasFoundNotification $notification) use ($msg) {
            $slack = $notification->toSlack();
            $this->assertStringContainsString($msg, $slack->content);
            $this->assertNull(collect($slack->attachments)->firstWhere('title', 'Health check'));
            $this->assertNull(collect($slack->attachments)->firstWhere('title', 'Exception message'));
            $this->assertNull(collect($slack->attachments)->firstWhere('title', 'Exception trace'));

            $mail = $notification->toMail();
            $this->assertNotNull(collect($mail->introLines)->first($this->searchString($msg)));
            $this->assertNull(collect($mail->introLines)->first($this->searchString('Health check:')));
            $this->assertNull(collect($mail->introLines)->first($this->searchString('Exception message:')));
            $this->assertNull(collect($mail->introLines)->first($this->searchString('Exception trace:')));

            return true;
        });
    }

    /** @test **/
    public function it_sends_an_notification_containing_the_exception_for_unexpected_health_check_errors()
    {
        Notification::fake();

        $this
            ->fakeBackup()
            ->makeHealthCheckFail()
            ->artisan('backup:monitor')
            ->assertExitCode(1);

        Notification::assertSentTo(new Notifiable(), UnhealthyBackupWasFoundNotification::class, function (UnhealthyBackupWasFoundNotification $notification) {
            $slack = $notification->toSlack();
            $this->assertStringContainsString(trans('backup::notifications.unhealthy_backup_found_unknown'), $slack->content);
            $this->assertNotNull(collect($slack->attachments)->firstWhere('title', 'Health check'));
            $this->assertNotNull(collect($slack->attachments)->firstWhere('title', 'Exception message'));
            $this->assertNotNull(collect($slack->attachments)->firstWhere('title', 'Exception trace'));

            $mail = $notification->toMail();
            $this->assertNotNull(collect($mail->introLines)->first($this->searchString(trans('backup::notifications.unhealthy_backup_found_unknown'))));
            $this->assertNotNull(collect($mail->introLines)->first($this->searchString('Health check: ')));
            $this->assertNotNull(collect($mail->introLines)->first($this->searchString('Exception trace: ')));

            return true;
        });
    }

    protected function makeHealthCheckFail(Exception $customException = null)
    {
        FakeFailingHealthCheck::$reason = $customException;

        config()->set('backup.monitor_backups.0.health_checks', [FakeFailingHealthCheck::class]);

        return $this;
    }

    protected function searchString($string)
    {
        return function ($text) use ($string) {
            return Str::contains($text, $string);
        };
    }

    protected function fakeBackup()
    {
        $this->createFileOnDisk('local', 'mysite/test1.zip', now()->subSecond());

        return $this;
    }
}

class FakeFailingHealthCheck extends HealthCheck
{
    public static $reason;

    public function checkHealth(BackupDestination $backupDestination)
    {
        throw (static::$reason ?: new Exception('dummy exception message'));
    }
}
