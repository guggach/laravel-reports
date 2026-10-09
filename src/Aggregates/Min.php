<?php

declare(strict_types=1);

namespace Guggach\Reports\Aggregates;

final class Min implements Aggregate
{
    private mixed $value = null;

    public function add(mixed $value): void
    {
        if ($value === null) {
            return;
        }

        if ($this->value === null || $value < $this->value) {
            $this->value = $value;
        }
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function reset(): void
    {
        $this->value = null;
    }
}
