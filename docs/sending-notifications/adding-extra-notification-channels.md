---
title: Adding extra notification channels
weight: 2
---

By default the package send notifications via email or Slack. It's easy to add an extra notification channel such as Telegram or native mobile push notification, etc.
 
The Laravel community is awesome. Shortly after Laravel 5.3 was released various developers worked together to create 30+ notification channels. You can view them all on [http://laravel-notification-channels.com](http://laravel-notification-channels.com).
 
In the following example we're going to add the Pusher push notifications channel. Other notification drivers can be added in the same way.

### 1. Install the notification channel driver

For Pusher Push notifications, require this package

```php
laravel-notification-channels/pusher-push-notifications
```

After composer has pulled in the package, just follow [the installation instructions of the package](https://github.com/laravel-notification-channels#installation) to complete the installation.


### 2. Creating your own custom notification

Let say you want to be notified via Pusher push notifications when a backup fails. To make this happen you'll need to create your own `BackupFailed` notification class like the one below:

```php
namespace App\Notifications;

use Spatie\Backup\Notifications\Notifications\BackupHasFailed as BaseNotification;
use NotificationChannels\PusherPushNotifications\Message;

class BackupHasFailed extends BaseNotification
{
    public function toPushNotification($notifiable)
    {
        return Message::create()
            ->iOS()
            ->badge(1)
            ->sound('fail')
            ->body("The backup of {$this->applicationName()} to disk {$this->diskName()} has failed");
    }
}
```

### 3. Register your custom notification in the config file

The last thing you need to do is register your custom notification in the config file.

```php
// config/backup.php
use \NotificationChannels\PusherPushNotifications\Channel as PusherChannel

...

    'notifications' => [

        'notifications' => [
            \App\Notifications\BackupHasFailed::class => ['mail', 'slack', PusherChannel::class],
            ...
```

