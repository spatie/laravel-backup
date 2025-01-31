<?php

namespace Spatie\Backup\Support;

class ConfigMerger
{
    public static function merge(array $config, array $defaultConfig): array
    {
        $result = $defaultConfig;
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                if (empty($value)) {
                    $result[$key] = [];

                    continue;
                }

                if (isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::merge($value, $result[$key]);

                    continue;
                }
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
