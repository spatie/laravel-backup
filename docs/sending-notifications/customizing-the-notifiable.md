---
title: Customizing the notifiable
weight: 3
---

Laravel 5.3's notifications are sent to a notifiable. A notifiable provides configuration values that determine how notifications will be sent. 

By default the package uses this notifiable class: `\Spatie\Backup\Notifications\Notifiable`. This class will read out the config file. All mail notifications will be sent to the mail address specified in the `notifications.mail.to` key of the config file.

If you use a channel that needs some get some extra information out of the notifiable you can easily extend the default notifiable.

Here's how that might look like:

```php
namespace App\Notifications;

use Spatie\Backup\Notifications\Notifiable;

class BackupNotifiable extends Notifiable
{
    public function routeNotificationForAnotherNotificationChannel()
    {
        return config('backup.notifications.another_notification_channel.property');
    }
}

```

Don't forget to register the notifiable in the config file:

```php
// config/backup.php
    'notifications' => [
    ...

        'notifiable' => App\Notifications\BackupNotifiable::class,    
```



