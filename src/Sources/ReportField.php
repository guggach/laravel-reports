<?php

declare(strict_types=1);

namespace Guggach\Reports\Sources;

final readonly class ReportField
{
    /**
     * @param  array<array-key, mixed>  $options
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $type = 'string',
        public bool $sortable = true,
        public bool $filterable = true,
        public bool $aggregatable = false,
        public bool $hidden = false,
        public ?string $format = null,
        public array $options = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $rawOptions = $data['options'] ?? null;
        $rawFormat = $data['format'] ?? null;

        return new self(
            key: self::toString($data['key'] ?? null),
            label: self::toString($data['label'] ?? null),
            type: self::toString($data['type'] ?? null, 'string'),
            sortable: (bool) ($data['sortable'] ?? true),
            filterable: (bool) ($data['filterable'] ?? true),
            aggregatable: (bool) ($data['aggregatable'] ?? false),
            hidden: (bool) ($data['hidden'] ?? false),
            format: $rawFormat === null ? null : self::toString($rawFormat),
            options: is_array($rawOptions) ? $rawOptions : [],
        );
    }

    private static function toString(mixed $value, string $default = ''): string
    {
        return match (true) {
            is_string($value) => $value,
            is_int($value), is_float($value) => (string) $value,
            is_bool($value) => $value ? '1' : '0',
            default => $default,
        };
    }
}
