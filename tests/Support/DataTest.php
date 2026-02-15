<?php

use Illuminate\Contracts\Support\Arrayable;
use Spatie\Backup\Support\Data;

it('converts public properties to array', function () {
    $data = new class extends Data
    {
        public function __construct(
            public string $name = 'test',
            public int $age = 25,
        ) {}
    };

    expect($data->toArray())->toBe([
        'name' => 'test',
        'age' => 25,
    ]);
});

it('recursively converts nested Arrayable objects', function () {
    $inner = new class extends Data
    {
        public function __construct(
            public string $value = 'nested',
        ) {}
    };

    $outer = new class($inner) extends Data
    {
        public function __construct(
            public Arrayable $child,
            public string $name = 'parent',
        ) {}
    };

    expect($outer->toArray())->toBe([
        'child' => ['value' => 'nested'],
        'name' => 'parent',
    ]);
});

it('handles null values', function () {
    $data = new class extends Data
    {
        public function __construct(
            public ?string $name = null,
            public ?int $age = null,
        ) {}
    };

    expect($data->toArray())->toBe([
        'name' => null,
        'age' => null,
    ]);
});
