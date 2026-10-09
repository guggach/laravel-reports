<?php

declare(strict_types=1);

namespace Guggach\Reports\Aggregates;

final class Count implements Aggregate
{
    private int $count = 0;

    public function add(mixed $value): void
    {
        if ($value !== null) {
            $this->count++;
        }
    }

    public function value(): int
    {
        return $this->count;
    }

    public function reset(): void
    {
        $this->count = 0;
    }
}
