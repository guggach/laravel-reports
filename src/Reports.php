<?php

declare(strict_types=1);

namespace Guggach\Reports;

use Guggach\Reports\Definition\ReportDefinition;
use Guggach\Reports\Engine\ReportEngine;

final readonly class Reports
{
    public function __construct(
        private ReportEngine $engine,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function render(Report $report, array $filters = []): mixed
    {
        return $this->engine->render($report, $filters);
    }

    public function definition(Report $report): ReportDefinition
    {
        return $this->engine->definition($report);
    }
}
