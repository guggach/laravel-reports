<?php

declare(strict_types=1);

namespace Guggach\Reports\Engine;

use Guggach\Reports\Contracts\ReportRenderer;
use Guggach\Reports\Definition\ReportDefinition;
use Guggach\Reports\Report;
use RuntimeException;

final readonly class ReportEngine
{
    public function __construct(
        private ?ReportRenderer $renderer = null,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function render(Report $report, array $filters = []): mixed
    {
        if (! $this->renderer instanceof ReportRenderer) {
            throw new RuntimeException(
                'No report renderer is configured yet; guggach/laravel-reports is work in progress.'
            );
        }

        return $this->renderer->render($report->withFilters($filters));
    }

    public function definition(Report $report): ReportDefinition
    {
        return $report->definition();
    }
}
