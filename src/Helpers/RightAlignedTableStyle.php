<?php

namespace Spatie\Backup\Helpers;

use Symfony\Component\Console\Helper\TableStyle;

class RightAlignedTableStyle extends TableStyle
{
    public function __construct()
    {
        $this->setPadType(STR_PAD_LEFT);
    }
}
