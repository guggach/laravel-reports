<?php

declare(strict_types=1);

namespace Guggach\Reports\Engine;

use Guggach\Reports\Aggregates\Aggregate;
use Guggach\Reports\Aggregates\Avg;
use Guggach\Reports\Aggregates\Count;
use Guggach\Reports\Aggregates\CountDistinct;
use Guggach\Reports\Aggregates\Max;
use Guggach\Reports\Aggregates\Min;
use Guggach\Reports\Aggregates\Sum;
use Guggach\Reports\Support\Record;

/**
 * Accumulates one or more aggregates over a set of records. Used for report
 * scope and for each open group level.
 */
final class AggregateResolver
{
    /** @var array<string, array{aggregate: Aggregate, field: string|null}> */
    private array $bindings = [];

    public static function make(string $type): Aggregate
    {
        return match ($type) {
            'avg' => new Avg,
            'count' => new Count,
            'countDistinct', 'count_distinct' => new CountDistinct,
            'min' => new Min,
            'max' => new Max,
            default => new Sum,
        };
    }

    public function bind(string $as, Aggregate $aggregate, ?string $field = null): self
    {
        $this->bindings[$as] = ['aggregate' => $aggregate, 'field' => $field];

        return $this;
    }

    /**
     * @param  list<array<string, mixed>>  $specs  [type, field, as]
     */
    public function bindGroupSpecs(array $specs): self
    {
        foreach ($specs as $spec) {
            $type = is_string($spec['type'] ?? null) ? $spec['type'] : 'sum';
            $as = is_string($spec['as'] ?? null) ? $spec['as'] : 'aggregate';
            $field = is_string($spec['field'] ?? null) ? $spec['field'] : null;

            $this->bind($as, self::make($type), $field);
        }

        return $this;
    }

    /**
     * @param  list<array<string, mixed>>  $specs  [name, class, field, scope]
     */
    public function bindReportSpecs(array $specs): self
    {
        foreach ($specs as $spec) {
            $class = $spec['class'] ?? null;

            if (! is_string($class) || ! class_exists($class)) {
                continue;
            }

            $aggregate = new $class;

            if (! $aggregate instanceof Aggregate) {
                continue;
            }

            $as = is_string($spec['name'] ?? null) ? $spec['name'] : 'aggregate';
            $field = is_string($spec['field'] ?? null) ? $spec['field'] : null;

            $this->bind($as, $aggregate, $field);
        }

        return $this;
    }

    public function add(mixed $record): void
    {
        foreach ($this->bindings as ['aggregate' => $aggregate, 'field' => $field]) {
            $aggregate->add($field === null ? 1 : Record::get($record, $field));
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function values(): array
    {
        $values = [];

        foreach ($this->bindings as $as => ['aggregate' => $aggregate]) {
            $values[$as] = $aggregate->value();
        }

        return $values;
    }

    public function reset(): void
    {
        foreach ($this->bindings as ['aggregate' => $aggregate]) {
            $aggregate->reset();
        }
    }
}
