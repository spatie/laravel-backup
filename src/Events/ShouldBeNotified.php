<?php

namespace Spatie\Backup\Events;

interface ShouldBeNotified
{
    public function shouldBeNotified() : bool;
}
