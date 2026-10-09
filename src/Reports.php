<?php

declare(strict_types=1);

namespace Guggach\Reports;

use Guggach\Reports\Definition\PageSetup;
use Guggach\Reports\Definition\ReportDefinition;
use Guggach\Reports\Engine\ReportEngine;
use Stringable;

final readonly class Reports
{
    public function __construct(
        private ReportEngine $engine,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $options
     */
    public function render(Report $report, array $filters = [], array $options = []): mixed
    {
        return $this->engine->render($report, $filters, $options);
    }

    /**
     * Render and return the HTML string.
     *
     * @param  array<string, mixed>  $filters
     * @param  array<string, mixed>  $options
     */
    public function html(Report $report, array $filters = [], array $options = []): string
    {
        $result = $this->engine->render($report, $filters, $options);

        if (is_string($result)) {
            return $result;
        }

        if (is_scalar($result) || $result instanceof Stringable) {
            return (string) $result;
        }

        return '';
    }

    public function definition(Report $report): ReportDefinition
    {
        return $this->engine->definition($report);
    }

    /**
     * The effective page setup (Config -> Layout -> Report). The preview frame
     * uses it to size the on-screen paper sheet.
     */
    public function pageSetup(Report $report): PageSetup
    {
        return $this->engine->pageSetup($report);
    }
}
