<?php

use Carbon\Carbon;
use Spatie\Backup\Helpers\Format;

it('can determine a human readable filesize', function () {
    expect(Format::humanReadableSize(10))->toEqual('10 B');
    expect(Format::humanReadableSize(100))->toEqual('100 B');
    expect(Format::humanReadableSize(1000))->toEqual('1000 B');
    expect(Format::humanReadableSize(10000))->toEqual('9.77 KB');
    expect(Format::humanReadableSize(1000000))->toEqual('976.56 KB');
    expect(Format::humanReadableSize(10000000))->toEqual('9.54 MB');
    expect(Format::humanReadableSize(10000000000))->toEqual('9.31 GB');
});

it('can determine the age in days', function () {
    Carbon::setTestNow(Carbon::create(2016, 1, 1)->startOfDay());

    expect(Format::ageInDays(Carbon::now()->subSeconds(5)))->toEqual('0.00 (5 seconds ago)');
    expect(Format::ageInDays(Carbon::now()->subHour()))->toEqual('0.04 (1 hour ago)');
    expect(Format::ageInDays(Carbon::now()->subHour()->subDay()))->toEqual('1.04 (1 day ago)');
    expect(Format::ageInDays(Carbon::now()->subHour()->subMonths(1)))->toEqual('30.04 (4 weeks ago)');
});
