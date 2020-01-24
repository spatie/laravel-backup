---
title: Secure MySQL connections
weight: 2
---
While recent `mysqldump` versions provided by the `mysql-client` package
[automatically establish a secured connection](https://dev.mysql.com/doc/refman/5.7/en/connection-options.html#option_general_ssl),
the `mysqldump` utility provided by `mariadb-client` does not operate in the same way.

Instead, you need to explicitly specify that it should establish a secure connection.

To do so, add extra options in your database connection configuration like so:

```php
<?php

return [
  // ...
  'connections' => [
    // ...
    'mysql' => [
      // ...
      'options' => [
        // ...
        // The regular SSL options
        PDO::MYSQL_ATTR_SSL_CA                 => storage_path('ca.pem'),
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
      ],
      
      'dump' => [
        // ...
        'addExtraOption' => '--ssl',
      ],
    ]
  ]
];
```
