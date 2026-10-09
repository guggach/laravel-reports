<?php

declare(strict_types=1);

namespace Guggach\Reports\Aggregates;

final class CountDistinct implements Aggregate
{
    /** @var array<int|string, true> */
    private array $seen = [];

    public function add(mixed $value): void
    {
        if ($value === null) {
            return;
        }

        if (is_int($value) || is_string($value)) {
            $this->seen[$value] = true;

            return;
        }

        if (is_scalar($value)) {
            $this->seen[(string) $value] = true;
        }
    }

    public function value(): int
    {
        return count($this->seen);
    }

    public function reset(): void
    {
        $this->seen = [];
    }
}
