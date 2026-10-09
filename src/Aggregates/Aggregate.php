<?php

declare(strict_types=1);

namespace Guggach\Reports\Aggregates;

interface Aggregate
{
    public function add(mixed $value): void;

    public function value(): mixed;

    public function reset(): void;
}
