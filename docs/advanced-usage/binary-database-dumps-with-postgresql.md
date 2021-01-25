---
title: Binary database dumps with PostgreSQL
weight: 3
---

PostgreSQL has the ability to produce binary database dumps via the `pg_dump` command, which produce smaller files than the SQL format and are faster to restore. See the [full list](https://www.postgresql.org/docs/current/app-pgdump.html) of `pg_dump` flags.

To take advantage of this, you can set the extra flags for `pg_dump` on the database connection(s) in  `app/config/database.php`.

```php
//config/database.php
'connections' => [
	'pgsql' => [
		'driver'    => 'pgsql'
		...,
		'dump' => [
		    ...,
		    'add_extra_option' => '--format=c', // and any other pg_dump flags
		]
	],
```

Additionally, you can change the file extension of the database dump file to signify that it is not a text SQL file.

```php
//config/backup.php
'backup' => [
    ...,
    'database_dump_file_extension' => 'backup', // produces a FILENAME.backup database dump
  ],
```
