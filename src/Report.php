<?php

declare(strict_types=1);

namespace Guggach\Reports;

use Guggach\Reports\Definition\ReportBuilder;
use Guggach\Reports\Definition\ReportDefinition;
use Guggach\Reports\Sources\ReportSource;

abstract class Report
{
    /** @var array<string, mixed> */
    private array $filters = [];

    abstract public function key(): string;

    abstract public function name(): string;

    /** The data source of this report (report-local, never shared). */
    abstract public function source(): ReportSource;

    abstract public function define(ReportBuilder $builder): void;

    public function layout(): string
    {
        return 'default';
    }

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
