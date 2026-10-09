<?php

declare(strict_types=1);

namespace Guggach\Reports\Engine;

use Guggach\Reports\Definition\ReportDefinition;
use Guggach\Reports\Report;
use Guggach\Reports\Support\Record;

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
     * @param  list<array{level: int, keys: list<string>, key: string}>  $groups
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

    /**
     * @param  list<array{level: int, keys: list<string>, key: string}>  $groups
     */
    public function withGroups(array $groups): self
    {
        return new self(
            report: $this->report,
            definition: $this->definition,
            record: $this->record,
            index: $this->index,
            total: $this->total,
            filters: $this->filters,
            locale: $this->locale,
            groups: $groups,
            aggregates: $this->aggregates,
            page: $this->page,
        );
    }

    /**
     * @param  array<string, mixed>  $aggregates
     */
    public function withAggregates(array $aggregates): self
    {
        return new self(
            report: $this->report,
            definition: $this->definition,
            record: $this->record,
            index: $this->index,
            total: $this->total,
            filters: $this->filters,
            locale: $this->locale,
            groups: $this->groups,
            aggregates: $aggregates,
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

    /**
     * The innermost currently open group, if any.
     *
     * @return array{level: int, keys: list<string>, key: string}|null
     */
    public function currentGroup(): ?array
    {
        if ($this->groups === []) {
            return null;
        }

        return $this->groups[array_key_last($this->groups)];
    }

    public function field(string $key, mixed $default = null): mixed
    {
        return Record::get($this->record, $key, $default);
    }
}
