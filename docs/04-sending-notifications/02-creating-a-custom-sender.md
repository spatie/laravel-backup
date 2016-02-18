---
title: Creating a custom sender
---

By default the backup package can notify you by writing something in the log, by sending a mail, or,
(when the `maknz/slack`) is installed, posting a message on Slack.

If you want to be notified via another channel you can create your own sender. A valid sender is any
object that implements to the `Spatie\Backup\Notifications\SendsNotifications`-interface.

This is what that interface looks like:

```php
namespace Spatie\Backup\Notifications;

interface SendsNotifications
{
    public function setType(string $type) : SendsNotifications;

    public function setSubject(string $subject) : SendsNotifications;

    public function setMessage(string $message) : SendsNotifications;

    public function send();
}
```

If you choose to extend `Spatie\Backup\Notifications\BaseSender` you'll only need to implement the `send`-function.

Your custom sender can be used by specifying it's full class name in one the `monitor.events`-keys in the laravel-backup
config file.

```php
...
'whenBackupHasFailed' => ['log', 'mail', App\BlaBla\MyCustomSender::class],
...
```

When you've created a sender that could be beneficial, consider [contributing](link naar contribution guidelines)
the code to this package.



