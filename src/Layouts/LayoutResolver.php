<?php

declare(strict_types=1);

namespace Guggach\Reports\Layouts;

use Guggach\Reports\Definition\PageSetup;
use Guggach\Reports\Definition\ReportDefinition;
use Guggach\Reports\Report;

/**
 * Resolves the layout of a report and the effective page setup. Shared by the
 * renderer and the preview frame so both agree on the paper geometry.
 */
final class LayoutResolver
{
    public function resolve(Report $report, ReportDefinition $definition): ?Layout
    {
        $reference = $this->reference($report, $definition);

        if (class_exists($reference) && is_subclass_of($reference, Layout::class)) {
            return new $reference;
        }

        return null;
    }

    /**
     * The report's own layout() wins over the builder value when set.
     */
    public function reference(Report $report, ReportDefinition $definition): string
    {
        $layout = $report->layout();

        return $layout !== 'default' ? $layout : $definition->layout;
    }

    /**
     * Config -> Layout -> Report (whole-object precedence for now).
     */
    public function pageSetup(Report $report, ?Layout $layout = null): PageSetup
    {
        return $report->pageSetup()
            ?? $layout?->pageSetup()
            ?? PageSetup::fromConfig(config('reports.paper'));
    }
}
