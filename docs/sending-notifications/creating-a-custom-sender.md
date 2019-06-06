---
title: Creating a custom sender
weight: 2
---

By default the backup package can notify you by:
- writing something in the log
- by sending a mail
- if `maknz/slack` is installed, posting a message on [Slack](https://slack.com)

If you want to be notified via another channel you can create your own sender. A valid sender is any object that implements the `Spatie\Backup\Notifications\SendsNotifications`-interface.

```php
namespace Spatie\Backup\Notifications;

interface SendsNotifications
{
    /**
     * @param string $type
     *
     * @return \Spatie\Backup\Notifications\SendsNotifications
     */
    public function setType($type);

    /**
     * @param string $subject
     *
     * @return \Spatie\Backup\Notifications\SendsNotifications
     */
    public function setSubject($subject);

    /**
     * @param string $message
     *
     * @return \Spatie\Backup\Notifications\SendsNotifications
     */
    public function setMessage($message);

    public function send();
}
```

If you choose to extend `Spatie\Backup\Notifications\BaseSender` you'll only need to implement the `send`-function.

Your custom sender can be used by specifying it's full class name in one the `monitor.events`-keys in the laravel-backup config file.

```php
// ...
'whenBackupHasFailed' => ['log', 'mail', App\Backup\MyCustomSender::class],
// ...
```

When you've created a sender that could be beneficial to the community, consider [contributing](https://github.com/spatie/laravel-backup/blob/master/CONTRIBUTING.md) the code to this package.
