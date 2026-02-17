<?php

use Illuminate\Console\Command;
use Spatie\Backup\Helpers\ConsoleOutput;

it('silently ignores calls when no command is set', function () {
    $consoleOutput = new ConsoleOutput;

    $consoleOutput->info('test');

    expect(true)->toBeTrue();
});

it('delegates calls to the command', function () {
    $consoleOutput = new ConsoleOutput;

    $command = Mockery::mock(Command::class);
    $command->shouldReceive('info')->once()->with('hello');

    $consoleOutput->setCommand($command);
    $consoleOutput->info('hello');
});
