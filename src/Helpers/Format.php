<?php

namespace Spatie\Backup\Helpers;

use Carbon\Carbon;

class Format
{
    public static function humanReadableSize(float $sizeInBytes): string
    {
        $units = [trans('file_sizes.B'), trans('file_sizes.KB'), trans('file_sizes.MB'), trans('file_sizes.GB'), trans('file_sizes.TB')];

        if ($sizeInBytes === 0) {
            return '0 '.$units[1];
        }
        for ($i = 0; $sizeInBytes > 1024; $i++) {
            $sizeInBytes /= 1024;
        }

        return round($sizeInBytes, 2).' '.$units[$i];
    }

    public static function emoji(bool $bool): string
    {
        return $bool
            ? '✅'
            : '❌';
    }

    public static function ageInDays(Carbon $date): string
    {
        return number_format(round($date->diffInMinutes() / (24 * 60), 2), 2).' ('.$date->diffForHumans().')';
    }
}
