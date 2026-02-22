<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Spatie\Backup\Events\UnhealthyBackupWasFound;
use Spatie\Backup\Exceptions\InvalidHealthCheck;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;

it('will fire an event on failed health check', function () {
    Event::fake();

    $this
        ->fakeBackup()
        ->makeHealthCheckFail()
        ->artisan('backup:monitor')
        ->assertExitCode(1);

    Event::assertDispatched(UnhealthyBackupWasFound::class);
});

it('sends an notification containing the exception message for handled health check errors', function () {
    Notification::fake();

    $this
        ->fakeBackup()
        ->makeHealthCheckFail(new InvalidHealthCheck($msg = 'This is the failure reason sent to the user'))
        ->artisan('backup:monitor')->assertExitCode(1);

    Notification::assertSentTo(
        new Notifiable,
        UnhealthyBackupWasFoundNotification::class,
        function (UnhealthyBackupWasFoundNotification $notification) use ($msg) {
            if (class_exists(\Illuminate\Notifications\Messages\SlackMessage::class)) {
                $slack = $notification->toSlack();
                expect($slack->content)->toContain($msg);
                expect(collect($slack->attachments)->firstWhere('title', 'Health check'))->toBeNull();
                expect(collect($slack->attachments)->firstWhere('title', 'Exception message'))->toBeNull();
                expect(collect($slack->attachments)->firstWhere('title', 'Exception trace'))->toBeNull();
            }

            $mail = $notification->toMail();
            expect($mail->introLines)->hasItemContaining($msg);
            expect($mail->introLines)->each->not()->toContain('Health check:');
            expect($mail->introLines)->each->not()->toContain('Exception message:');
            expect($mail->introLines)->each->not()->toContain('Exception trace:');

            return true;
        }
    );
});

it('sends an notification containing the exception for unexpected health check errors', function () {
    Notification::fake();

    $this
        ->fakeBackup()
        ->makeHealthCheckFail()
        ->artisan('backup:monitor')
        ->assertExitCode(1);

    Notification::assertSentTo(new Notifiable, UnhealthyBackupWasFoundNotification::class, function (UnhealthyBackupWasFoundNotification $notification) {
        if (class_exists(\Illuminate\Notifications\Messages\SlackMessage::class)) {
            $slack = $notification->toSlack();
            expect($slack->content)->toContain('dummy exception message');
            expect(collect($slack->attachments)->firstWhere('title', 'Health check'))->toBeNull();
            expect(collect($slack->attachments)->firstWhere('title', 'Exception message'))->toBeNull();
            expect(collect($slack->attachments)->firstWhere('title', 'Exception trace'))->toBeNull();
        }

        $mail = $notification->toMail();

        expect($mail->introLines)
            ->hasItemContaining('dummy exception message');

        return true;
    });
});
