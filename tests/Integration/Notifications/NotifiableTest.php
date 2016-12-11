<?php

namespace Spatie\Backup\Test\Integration\Notifications;

use Mail;
use Exception;
use Illuminate\Notifications\Notifiable;
use MailThief\Testing\InteractsWithMail;
use Spatie\Backup\Events\BackupHasFailed;
use Spatie\Backup\Test\Integration\TestCase;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class NotifiableTest extends TestCase
{
    use InteractsWithMail;

    /** @test */
    public function it_will_use_the_config_values_by_default_to_send_notifications()
    {
        $this->fireBackupHasFailedEvent();

        $this->seeMessageFor(config('laravel-backup.notifications.mail.to'));
    }

    /** @test */
    public function an_alternative_notifiable_can_be_set()
    {
        $notifiable = new class {
            use Notifiable;

            public function routeNotificationForMail()
            {
                return 'alternative@email.com';
            }
        };

        $this->app['config']->set('laravel-backup.notifications.notifiable', get_class($notifiable));

        $this->fireBackupHasFailedEvent();

        $this->seeMessageFor('alternative@email.com');
    }

    protected function fireBackupHasFailedEvent()
    {
        $exception = new Exception('Dummy exception');

        $backupDestination = BackupDestinationFactory::createFromArray(config('laravel-backup.backup'))->first();

        event(new BackupHasFailed($exception, $backupDestination));
    }
}
