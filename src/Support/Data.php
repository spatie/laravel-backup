<?php

namespace Spatie\Backup\Support;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use ReflectionProperty;

/** @implements Arrayable<string, mixed> */
class Data implements Arrayable
{
    public function toArray(): array
    {
        $array = [];
        $reflectionClass = new ReflectionClass($this);

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $value = $this->$name;

            if ($value instanceof Arrayable) {
                $array[$name] = $value->toArray();
            } else {
                $array[$name] = $value;
            }
        }

        return $array;
    }
}
