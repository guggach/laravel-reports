<?php

declare(strict_types=1);

namespace Guggach\Reports\Sources;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Collection;

abstract class ReportSource
{
    /**
     * @return Builder|Collection<int, mixed>
     */
    abstract public function query(): Builder|Collection;

    /**
     * @return list<ReportField>
     */
    abstract public function fields(): array;

    public function key(): string
    {
        return static::class;
    }

    /**
     * @return list<ReportParameter>
     */
    public function parameters(): array
    {
        return [];
    }
}
