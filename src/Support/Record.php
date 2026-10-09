<?php

declare(strict_types=1);

namespace Guggach\Reports\Support;

use ArrayAccess;
use Stringable;

/**
 * Reads a value from a record that may be an array, ArrayAccess or object.
 */
final class Record
{
    public static function get(mixed $record, string $key, mixed $default = null): mixed
    {
        if (is_array($record)) {
            return $record[$key] ?? $default;
        }

        if ($record instanceof ArrayAccess) {
            return $record->offsetExists($key) ? $record[$key] : $default;
        }

        if (is_object($record)) {
            return property_exists($record, $key) ? $record->{$key} : $default;
        }

        return $default;
    }

    public static function string(mixed $record, string $key, string $default = ''): string
    {
        $value = self::get($record, $key, $default);

        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return $default;
    }
}
