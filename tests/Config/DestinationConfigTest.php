<?php

use Spatie\Backup\Config\DestinationConfig;
use Spatie\Backup\Exceptions\InvalidConfig;
use ZipArchive;

it('creates config with defaults', function () {
    $config = DestinationConfig::fromArray([]);

    expect($config->compressionMethod)->toBe(ZipArchive::CM_DEFAULT);
    expect($config->compressionLevel)->toBe(9);
    expect($config->filenamePrefix)->toBe('');
    expect($config->disks)->toBe(['local']);
    expect($config->continueOnFailure)->toBeFalse();
});

it('accepts boundary compression levels', function () {
    $configMin = DestinationConfig::fromArray(['compression_level' => 0]);
    expect($configMin->compressionLevel)->toBe(0);

    $configMax = DestinationConfig::fromArray(['compression_level' => 9]);
    expect($configMax->compressionLevel)->toBe(9);
});

it('rejects compression level below zero', function () {
    DestinationConfig::fromArray(['compression_level' => -1]);
})->throws(InvalidConfig::class, '`compression_level` must be between 0 and 9.');

it('rejects compression level above nine', function () {
    DestinationConfig::fromArray(['compression_level' => 10]);
})->throws(InvalidConfig::class, '`compression_level` must be between 0 and 9.');
