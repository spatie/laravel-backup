<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Notifications\BaseNotification;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Support\Data;

class NotificationsConfig extends Data
{
    /**
     * @param array<class-string<BaseNotification>, array<string>> $notifications
     * @param class-string<Notifiable> $notifiable
     */
    public function __construct(
        public array $notifications,
        public string $notifiable,
    ) {
        // @todo mail, slack, discord
    }
}
