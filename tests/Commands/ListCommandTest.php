<?php

it('can run the list command', function () {
    config()->set('backup.backup.destination.disks', [
        'local',
    ]);

    $this->artisan('backup:list')->assertExitCode(0);
});

it('warns_the_user_about_the_old_style_config_keys', function () {
    $this->artisan('backup:list')
        ->assertSuccessful();

    config(['backup.monitorBackups' => config('backup.monitor_backups')]);

    $this->artisan('backup:list')
        ->expectsOutput('Warning! Your config file still uses the old monitorBackups key. Update it to monitor_backups.');
});
