<?php

declare(strict_types=1);

namespace Guggach\Reports;

use Guggach\Reports\Definition\ReportBuilder;
use Guggach\Reports\Definition\ReportDefinition;

abstract class Report
{
    /** @var array<string, mixed> */
    private array $filters = [];

    abstract public function key(): string;

    abstract public function name(): string;

    abstract public function define(ReportBuilder $builder): void;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function withFilters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return $this->filters;
    }

    public function definition(): ReportDefinition
    {
        $builder = new ReportBuilder;

        $this->define($builder);

        return $builder->build();
    }
}
