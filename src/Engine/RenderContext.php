<?php

declare(strict_types=1);

namespace Guggach\Reports\Engine;

use ArrayAccess;
use Guggach\Reports\Definition\ReportDefinition;
use Guggach\Reports\Report;

/**
 * The data context handed to every band (Blade view or closure). Accessing an
 * unknown property falls back to the current record field, so a view may write
 * {{ $ctx->amount }} for an array/object record.
 *
 * @property-read mixed $record
 */
final readonly class RenderContext
{
    /**
     * @param  array<string, mixed>  $filters
     * @param  list<array<string, mixed>>  $groups
     * @param  array<string, mixed>  $aggregates
     * @param  array<string, mixed>  $page
     */
    public function __construct(
        public Report $report,
        public ReportDefinition $definition,
        public mixed $record = null,
        public int $index = 0,
        public int $total = 0,
        public array $filters = [],
        public ?string $locale = null,
        public array $groups = [],
        public array $aggregates = [],
        public array $page = [],
    ) {}

    public function __get(string $name): mixed
    {
        return $this->field($name);
    }

    public function __isset(string $name): bool
    {
        return $this->field($name) !== null;
    }

    public function withRecord(mixed $record, int $index): self
    {
        return new self(
            report: $this->report,
            definition: $this->definition,
            record: $record,
            index: $index,
            total: $this->total,
            filters: $this->filters,
            locale: $this->locale,
            groups: $this->groups,
            aggregates: $this->aggregates,
            page: $this->page,
        );
    }

    public function isFirst(): bool
    {
        return $this->index === 0;
    }

    public function isLast(): bool
    {
        return $this->index === $this->total - 1;
    }

    public function field(string $key, mixed $default = null): mixed
    {
        $record = $this->record;

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
}
