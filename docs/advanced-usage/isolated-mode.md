---
title: Isolated mode
weight: 5
---

If your application's scheduler is running on multiple servers, you may limit the backup job to only execute on a single server.

To indicate that the task should run on only one server, you may use the `--isolated` option when running the task on your server:

```php
php artisan backup:run --isolated
```

The first server to obtain the task will secure an atomic lock on the job to prevent other servers from running the same task at the same time.

> To utilize this feature, your application must be using the `database`, `memcached`, `dynamodb`, or `redis` cache driver as your application's default cache driver. In addition, all servers must be communicating with the same central cache server.

The following commands support the `--isolated` option:

- `backup:run`
- `backup:clean`
- `backup:monitor`
