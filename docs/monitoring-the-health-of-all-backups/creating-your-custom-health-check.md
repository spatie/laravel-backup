---
title: Creating your custom health check
weight: 2
---

 You can create your own custom health check by letting a class extend `Spatie\Backup\Tasks\Monitor\HealthCheck`. 
 
That base class contains one abstract method that you should implement.

```php
public function checkHealth(BackupDestination $backupDestination);
```
 
If your check determines that the backup is not healthy it should throw a `Spatie\Backup\Exceptions\InvalidHealthCheck` exception. The `HealthCheck` base class contains three helpful methods that helps you do this.

- `fail($message)`: will throw the right exception under the hood.
- `failIf(bool $condition, string $message)`: will throw the right exception if `$condition` is `true`
- `failUnless(bool $condition, string $message)`: will throw the right exception if `$condition` is `false`

You should register your custom health check in the `health_checks` key of the `backup.php` config file.

To see an example of a `HealthCheck`, go read the could of the `MaximumAgeInDays` and `MaximumStorageInMegabytes` health checks that are provided by the package.
