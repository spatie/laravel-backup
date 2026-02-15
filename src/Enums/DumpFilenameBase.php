<?php

namespace Spatie\Backup\Enums;

enum DumpFilenameBase: string
{
    case Database = 'database';
    case Connection = 'connection';
}
