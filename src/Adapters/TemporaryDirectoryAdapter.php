<?php

namespace Spatie\Backup\Adapters;

use Spatie\Backup\Contracts\TemporaryDirectory;
use Spatie\TemporaryDirectory\TemporaryDirectory as BaseTemporaryDirectory;

class TemporaryDirectoryAdapter extends BaseTemporaryDirectory implements TemporaryDirectory
{

}
