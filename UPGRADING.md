## From v8 to v9

- (All keys in the config file are still snake_cased. Rename any camelCased keys to their snake_cased counterparts)

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
