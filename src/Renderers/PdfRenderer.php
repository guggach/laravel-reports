<?php

declare(strict_types=1);

namespace Guggach\Reports\Renderers;

use Guggach\Reports\Contracts\HtmlToPdf;
use Guggach\Reports\Contracts\ReportRenderer;
use Guggach\Reports\Layouts\LayoutResolver;
use Guggach\Reports\Output\PdfOptions;
use Guggach\Reports\Report;

/**
 * Renders a report to PDF: it first renders the HTML document (same pipeline as
 * the HTML output) and then hands it to the bound HtmlToPdf converter.
 */
final readonly class PdfRenderer implements ReportRenderer
{
    public function __construct(
        private HtmlRenderer $html,
        private HtmlToPdf $converter,
        private LayoutResolver $layouts,
    ) {}

    public function format(): string
    {
        return 'pdf';
    }

    public function render(Report $report, array $options = []): string
    {
        $html = $this->html->render($report, $options);

        $definition = $report->definition();
        $pageSetup = $this->layouts->pageSetup($report, $this->layouts->resolve($report, $definition));

        return $this->converter->convert($html, PdfOptions::fromPageSetup($pageSetup, $options));
    }
}
