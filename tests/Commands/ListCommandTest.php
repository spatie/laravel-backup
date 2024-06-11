<?php

it('can run the list command', function () {
    config()->set('backup.backup.destination.disks', [
        'local',
    ]);

    $this->artisan('backup:list')->assertExitCode(0);
});
