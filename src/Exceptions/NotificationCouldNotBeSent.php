<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class NotificationCouldNotBeSent extends Exception
{
    public static function noNotificationClassForEvent($event): self
    {
        $eventClass = $event::class;

        return new static("There is no notification class that can handle event `{$eventClass}`.");
    }
}
