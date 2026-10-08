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
     * @param  array<string, mixed>  $options
     */
    public function render(Report $report, array $filters = [], array $options = []): mixed
    {
        if (! $this->renderer instanceof ReportRenderer) {
            throw new RuntimeException(
                'No report renderer is configured; bind Guggach\Reports\Contracts\ReportRenderer.'
            );
        }

        return $this->renderer->render($report->withFilters($filters), $options);
    }

    public function definition(Report $report): ReportDefinition
    {
        return $report->definition();
    }
}
