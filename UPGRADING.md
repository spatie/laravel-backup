## From v9 to v10

v10 requires PHP 8.4+ and Laravel 12+. If you are on an older version of PHP or Laravel, stay on v9.

- In `config/backup.php`, if you had `encryption` set to `null` or `false`, change it to `'none'`. The supported values are now `'none'`, `'default'`, `'aes128'`, `'aes192'`, `'aes256'`.

- All events now carry primitive properties (`string $diskName`, `string $backupName`) instead of `BackupDestination` or `BackupDestinationStatus` objects. Update your listeners:

```php
// before
$event->backupDestination->diskName();

// after
$event->diskName;
```

This applies to `BackupWasSuccessful`, `BackupHasFailed`, `CleanupWasSuccessful`, `CleanupHasFailed`, and `HealthyBackupWasFound`. For `UnhealthyBackupWasFound`, replace `$event->backupDestinationStatus` with `$event->diskName`, `$event->backupName`, and `$event->failureMessages`.

- The `consoleOutput()` helper has been replaced by `backupLogger()`. If you were calling `consoleOutput()` anywhere, replace it with `backupLogger()`.

- If you have a custom cleanup strategy extending `CleanupStrategy`, change the constructor to accept `Spatie\Backup\Config\Config` instead of `Illuminate\Contracts\Config\Repository`. Access config values via typed properties instead of array access:

```php
// before
$this->config->get('backup.cleanup.default_strategy.keep_all_backups_for_days');

// after
$this->config->cleanup->defaultStrategy->keepAllBackupsForDays;
```

- `BackupJob::disableNotifications()` has been removed. Use `EventHandler::disable()` instead. Note that events now always fire regardless of the `--disable-notifications` flag — only notification delivery is suppressed.

## From v8 to v9

- Ensure your config/backup.php file is in sync with the latest settings. You can copy paste the missing defaults from this [config file](https://github.com/spatie/laravel-backup/blob/main/config/backup.php).
- All keys in the config file are still snake_cased. Rename any camelCased keys to their snake_cased counterparts

## From v7 to v8

- There are no changes to the public API. You can upgrade without having to make any changes. The package now requires Laravel 9

## From v6 to v7

- All notification class names got suffixed with 'Notification'. Make sure you update the classnames where you are using these notifications.

# From v5 to v6

- All keys in the config file are now snake_cased. Rename any camelCased keys to their snake_cased counterparts.
- Make sure the structure of your config file is the same as the one that gets published by the package.
- The `health_checks` config keys now contain actual check classes. Modify your config file so it uses the actual check classes.

# From v4 to v5

The config file has been renamed. Change the name of the config file from `laravel-backup.php` to `backup.php`
