<?php

use Spatie\Backup\Notifications\Channels\Discord\DiscordMessage;

beforeEach(function () {
    $this->message = new DiscordMessage;
});

it('resizes embed title if character count is greater than 256', function () {
    $string = fake()->regexify('[A-Za-z0-9]{300}');
    $count = strlen($string);

    expect($count)->toBeGreaterThan(256);

    $this->message->title($string);
    $title = $this->message->toArray()['embeds'][0]['title'];
    $count = strlen($title);

    expect($count)->toBeLessThan(256);
});

it('resizes embed description if character count is greater than 4096', function () {
    $string = fake()->regexify('[A-Za-z0-9]{5000}');
    $count = strlen($string);

    expect($count)->toBeGreaterThan(4096);

    $this->message->title($string);
    $description = $this->message->toArray()['embeds'][0]['description'];
    $count = strlen($description);

    expect($count)->toBeLessThan(4096);
});

it('resizes fields if fields count is greater than 25', function () {
    $array = array_map(fn () => fake()->word(), range(1, 50));
    $count = count($array);

    expect($count)->toBeGreaterThan(25);

    $this->message->fields($array);
    $fields = $this->message->toArray()['embeds'][0]['fields'];
    $count = count($fields);

    expect($count)->toEqual(25);
});

it('resizes field name if character count is greater than 256 and value if character count is greater than 1024', function () {
    $name = fake()->regexify('[A-Za-z0-9]{300}');
    $value = fake()->regexify('[A-Za-z0-9]{2000}');
    $array = [$name => $value];
    $nameCount = strlen($name);
    $valueCount = strlen($value);

    expect($nameCount)->toBeGreaterThan(256);
    expect($valueCount)->toBeGreaterThan(1024);

    $this->message->fields($array);
    $fields = $this->message->toArray()['embeds'][0]['fields'][0];
    $nameCount = strlen($fields['name']);
    $valueCount = strlen($fields['value']);

    expect($nameCount)->toBeLessThan(256);
    expect($valueCount)->toBeLessThan(1024);
});

it('resizes footer text if character count is greater than 2048', function () {
    $string = fake()->regexify('[A-Za-z0-9]{3000}');
    $count = strlen($string);

    expect($count)->toBeGreaterThan(2048);

    $this->message->footer($string);
    $footer = $this->message->toArray()['embeds'][0]['footer']['text'];
    $count = strlen($footer);

    expect($count)->toBeLessThan(2048);
});
