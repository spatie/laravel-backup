<?php

namespace Spatie\Backup\Exceptions;

use Exception;

class NotificationCouldNotBeSent extends Exception
{
    public static function noNotificationClassForEvent(object $event): static
    {
        $eventClass = $event::class;

        return new static("There is no notification class that can handle event `{$eventClass}`.");
    }
}
