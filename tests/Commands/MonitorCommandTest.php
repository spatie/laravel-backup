<?php

namespace Spatie\Backup\Tests\Commands;

it('can run the monitor command', function () {
    $this->artisan('backup:monitor')->assertExitCode(0);
});
