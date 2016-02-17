---
title: Sending notifications
---

## Overview

The package can let you know that your backups are (not) ok. It can send notify you via one ore more channels
when a certain event takes place.

## Configuration

This is the portion of the configuration that will determine when and how notification will be sent.
Most options should be self-explanatory.

```php
    'notifications' => [

        /*
         * This class will be used to send all notifications.
         */
        'handler' => Spatie\Backup\Notifications\Notifier::class,

        /*
         * Here you can specify the ways you want to be notified when certain
         * events take place. Possible values are "log", "mail" and "slack".
         *
         * Slack requires the installation of the maknz/slack package
         */
        'events' => [
            'whenBackupWasSuccessful'     => ['log'],
            'whenCleanupWasSuccessful'    => ['log'],
            'whenHealthyBackupWasFound'   => ['log'],
            'whenBackupHasFailed'         => ['log', 'mail'],
            'whenCleanupHasFailed'        => ['log', 'mail'],
            'whenUnHealthyBackupWasFound' => ['log', 'mail']
        ],

        /*
         * Here you can specify how mails should be sent.
         */
        'mail' => [
            'from' => 'your@email.com',
            'to' => 'your@email.com',
        ],

        /*
         * Here you can how messages should be sent to Slack.
         */
        'slack' => [
            'channel'  => '#backups',
            'username' => 'Backup bot',
            'icon'     => ':robot:',
        ],
    ]
```

