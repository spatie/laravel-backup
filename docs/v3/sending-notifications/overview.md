---
title: Sending notifications
---

The package can let you know that your backups are (not) ok. It can notify you via one or more channels
when a certain event takes place.

## Configuration

This is the portion of the configuration that will determine when and how notifications will be sent.
Most options should be self-explanatory.

```php
//config/laravel-backup.php

    'notifications' => [

        /*
         * This class will be used to send all notifications.
         */
        'handler' => Spatie\Backup\Notifications\Notifier::class,

        /*
         * Here you can specify the ways you want to be notified when certain
         * events take place. Possible values are "log", "mail", "slack", "pushover" and "telegram".
         *
         * Slack requires the installation of the maknz/slack package.
         * Telegram requires the installation of the irazasyed/telegram-bot-sdk package.
         */
        'events' => [
            'whenBackupWasSuccessful'     => ['log'],
            'whenCleanupWasSuccessful'    => ['log'],
            'whenHealthyBackupWasFound'   => ['log'],
            'whenBackupHasFailed'         => ['log', 'mail'],
            'whenCleanupHasFailed'        => ['log', 'mail'],
            'whenUnhealthyBackupWasFound' => ['log', 'mail']
        ],

        /*
         * Here you can specify how emails should be sent.
         */
        'mail' => [
            'from' => 'your@email.com',
            'to'   => 'your@email.com',
        ],

        /*
         * Here you can specify how messages should be sent to Slack.
         */
        'slack' => [
            'channel'  => '#backups',
            'username' => 'Backup bot',
            'icon'     => ':robot:',
        ],
        
        /*
         * Here you can specify how messages should be sent to Pushover.
         */
        'pushover' => [
            'token'  => env('PUSHOVER_APP_TOKEN'),
            'user'   => env('PUSHOVER_USER_KEY'),
            'sounds' => [
                'success' => env('PUSHOVER_SOUND_SUCCESS','pushover'),
                'error'   => env('PUSHOVER_SOUND_ERROR','siren'),
            ],
        ],
        
        /*
         * Here you can specify how messages should be sent to Telegram Bot API.
         */
        'telegram' => [
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'chat_id'   => env('TELEGRAM_CHAT_ID'),
            'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),
            'disable_web_page_preview' => env('TELEGRAM_DISABLE_WEB_PAGE_PREVIEW', true),
        ],
    ]
```
