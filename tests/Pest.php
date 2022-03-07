<?php

use Spatie\Backup\Tests\TestCase;
use function PHPUnit\Framework\assertTrue;

uses(TestCase::class)->in(__DIR__);

expect()->extend('hasItemContaining', function(string $searchString) {
    foreach($this->value as $item) {
        if (str_contains($item, $searchString)) {
            expect(true)->toBeTrue();
            return $this;
        }
    }

    assertTrue(false, "Found no item containing `{$searchString}`");

    return $this;
});
