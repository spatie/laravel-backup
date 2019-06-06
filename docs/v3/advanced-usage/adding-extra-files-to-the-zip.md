---
title: Adding Extra Files to the zip
---

When performing a backup the package will create a zip file containing all files that need to be backed up and the dumped databases.

Right after the zip is created and before it is copied to any of the destination filesystems the `Spatie\Backup\Events\BackupZipWasCreated`-event will be fired. This is what is looks like:

```
namespace Spatie\Backup\Events;

use Spatie\Backup\Tasks\Backup\Zip;

class BackupZipWasCreated
{
    /** @var \Spatie\Backup\Tasks\Backup\Zip */
    public $zip;

    public function __construct(Zip $zip)
    {
        $this->zip = $zip;
    }
}
```

You can use that event to add extra files to the zip.

```php
$zip->add($file, $nameOfTheFileInsideTheZip);
```
