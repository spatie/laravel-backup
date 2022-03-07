<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Exceptions\InvalidHealthCheck;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;
use Spatie\Backup\Tasks\Monitor\HealthCheck;
use Spatie\Backup\Tests\TestCase;

uses(TestCase::class);

it('will fire an event on failed health check', function () {
    Event::fake();

    fakeBackup()
        ->makeHealthCheckFail()
        ->artisan('backup:monitor')
        ->assertExitCode(1);

    Event::assertDispatched(UnhealthyBackupWasFound::class);
});

it('sends an notification containing the exception message for handled health check errors', function () {
    Notification::fake();

    fakeBackup()
        ->makeHealthCheckFail(new InvalidHealthCheck($msg = 'This is the failure reason sent to the user'))
        ->artisan('backup:monitor')->assertExitCode(1);

    Notification::assertSentTo(
        new Notifiable(),
        UnhealthyBackupWasFoundNotification::class,
        function (UnhealthyBackupWasFoundNotification $notification) use ($msg) {
            $slack = $notification->toSlack();
            $this->assertStringContainsString($msg, $slack->content);
            $this->assertNull(collect($slack->attachments)->firstWhere('title', 'Health check'));
            $this->assertNull(collect($slack->attachments)->firstWhere('title', 'Exception message'));
            $this->assertNull(collect($slack->attachments)->firstWhere('title', 'Exception trace'));

            $mail = $notification->toMail();
            $this->assertNotNull(collect($mail->introLines)->first(searchString($msg)));
            $this->assertNull(collect($mail->introLines)->first(searchString('Health check:')));
            $this->assertNull(collect($mail->introLines)->first(searchString('Exception message:')));
            $this->assertNull(collect($mail->introLines)->first(searchString('Exception trace:')));

            return true;
        }
    );
});

it('sends an notification containing the exception for unexpected health check errors', function () {
    Notification::fake();

    fakeBackup()
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
        $this->assertNotNull(collect($mail->introLines)->first(searchString(trans('backup::notifications.unhealthy_backup_found_unknown'))));
        $this->assertNotNull(collect($mail->introLines)->first(searchString('Health check: ')));
        $this->assertNotNull(collect($mail->introLines)->first(searchString('Exception trace: ')));

        return true;
    });
});

// Helpers
function makeHealthCheckFail(Exception $customException = null)
{
    FakeFailingHealthCheck::$reason = $customException;

    config()->set('backup.monitor_backups.0.health_checks', [FakeFailingHealthCheck::class]);

    return $this;
}

function searchString($string)
{
    return function ($text) use ($string) {
        return Str::contains($text, $string);
    };
}

function fakeBackup()
{
    test()->createFileOnDisk('local', 'mysite/test1.zip', now()->subSecond());

    return $this;
}

function checkHealth(BackupDestination $backupDestination)
{
    throw (static::$reason ?: new Exception('dummy exception message'));
}
