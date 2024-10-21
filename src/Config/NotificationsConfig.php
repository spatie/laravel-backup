<?php

namespace Spatie\Backup\Config;

use Spatie\Backup\Exceptions\InvalidConfig;
use Spatie\Backup\Notifications\BaseNotification;
use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Support\Data;

class NotificationsConfig extends Data
{
    /**
     * @param  array<class-string<BaseNotification>, array<string>>  $notifications
     * @param  class-string<Notifiable>  $notifiable
     */
    protected function __construct(
        public array $notifications,
        public string $notifiable,
        public NotificationMailConfig $mail,
        public NotificationSlackConfig $slack,
        public ?NotificationDiscordConfig $discord,
    ) {
        if (! class_exists($this->notifiable)) {
            throw InvalidConfig::invalidStrategy($this->notifiable);
        }
    }

    /** @param array<mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            notifications: $data['notifications'],
            notifiable: $data['notifiable'],
            mail: NotificationMailConfig::fromArray($data['mail']),
            slack: NotificationSlackConfig::fromArray($data['slack']),
            discord: isset($data['discord']) ? NotificationDiscordConfig::fromArray($data['discord']) : null,
        );
    }
}
