<?php

namespace App\Notifications;

use Illuminate\Config\Repository;
use Illuminate\Notifications\Notification;
use Spatie\Backup\Notifications\PackageUser;

abstract class BaseNotification extends Notification
{
    /** @var \Spatie\Backup\Notifications\PackageUser */
    protected $packageUser;

    /** @var \Illuminate\Config\Repository */
    protected $config;

    public function __construct(PackageUser $packageUser, Repository $config)
    {
        $this->packageUser = $packageUser;

        $this->config = $config;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->config['laravel.backup.notifications.' . static::class];
    }
}
