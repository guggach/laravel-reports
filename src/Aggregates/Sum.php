<?php

declare(strict_types=1);

namespace Guggach\Reports\Aggregates;

final class Sum implements Aggregate
{
    private float|int $total = 0;

    public function add(mixed $value): void
    {
        if (is_numeric($value)) {
            $this->total += $value + 0;
        }
    }

    public function value(): float|int
    {
        return $this->total;
    }

    public function reset(): void
    {
        $this->total = 0;
    }
}
