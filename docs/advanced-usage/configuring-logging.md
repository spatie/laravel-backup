---
title: Configuring logging
weight: 6
---

The backup package logs activity messages (e.g. "Starting backup...", "Backup completed") to Laravel's default log channel. On applications that run backups frequently, these messages can clutter your log files.

You can control where backup log messages are written by setting the `logging.channel` option in `config/backup.php`.

## Using a specific log channel

To route backup logs to a dedicated channel, set `channel` to any channel name defined in your `config/logging.php`:

```php
// config/backup.php

'logging' => [
    'channel' => 'daily',
],
```

## Disabling logging

To disable backup logging entirely, set `channel` to `false`:

```php
// config/backup.php

'logging' => [
    'channel' => false,
],
```

When logging is disabled, error log lines are suppressed too. Failure notifications are handled separately via the `notifications` config and will still fire normally.

## Default behavior

When `channel` is set to `null` (the default), or when the `logging` key is missing from your config, the package uses Laravel's default log channel. This preserves the existing behavior for users who haven't updated their config file.

```php
// config/backup.php

'logging' => [
    'channel' => null,
],
```
