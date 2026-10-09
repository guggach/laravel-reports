<?php

declare(strict_types=1);

namespace Guggach\Reports\Aggregates;

final class Avg implements Aggregate
{
    private float|int $sum = 0;

    private int $count = 0;

    public function add(mixed $value): void
    {
        if (is_numeric($value)) {
            $this->sum += $value + 0;
            $this->count++;
        }
    }

    public function value(): float|int
    {
        return $this->count === 0 ? 0 : $this->sum / $this->count;
    }

    public function reset(): void
    {
        $this->sum = 0;
        $this->count = 0;
    }
}
