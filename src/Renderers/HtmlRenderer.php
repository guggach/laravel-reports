<?php

declare(strict_types=1);

namespace Guggach\Reports\Renderers;

use Closure;
use Guggach\Reports\Contracts\ReportRenderer;
use Guggach\Reports\Definition\Bands\Band;
use Guggach\Reports\Definition\ReportDefinition;
use Guggach\Reports\Engine\BandRenderer;
use Guggach\Reports\Engine\FlowPaginator;
use Guggach\Reports\Engine\LocaleScope;
use Guggach\Reports\Engine\RenderContext;
use Guggach\Reports\Report;
use Illuminate\View\Factory as ViewFactory;
use Stringable;

/**
 * Default renderer: assembles the bands into HTML and wraps them in the
 * report layout. Output adapters (PDF, ...) build on top of this.
 */
final readonly class HtmlRenderer implements BandRenderer, ReportRenderer
{
    public function __construct(
        private ViewFactory $views,
    ) {}

    public function format(): string
    {
        return 'html';
    }

    public function render(Report $report, array $options = []): string
    {
        $definition = $report->definition();

        $configured = $options['locale'] ?? config('reports.locale');
        $locale = is_string($configured) ? $configured : null;

        return (new LocaleScope($locale))->run(function () use ($report, $definition, $locale): string {
            $source = $report->source()->withLocale($locale);

            $records = $this->normalize($source->records());

            $context = new RenderContext(
                report: $report,
                definition: $definition,
                total: count($records),
                filters: $report->filters(),
                locale: $locale,
            );

            $content = (new FlowPaginator($this))->assemble($definition, $records, $context);

            return $this->renderLayout($definition, $content, $context);
        });
    }

    public function renderBand(Band $band, RenderContext $context): string
    {
        if ($band->closure instanceof Closure) {
            $result = ($band->closure)($context);

            if ($result === null) {
                return '';
            }

            if (is_string($result)) {
                return $result;
            }

            if (is_scalar($result) || $result instanceof Stringable) {
                return (string) $result;
            }

            return '';
        }

        if (is_string($band->view) && $band->view !== '') {
            return $this->renderView($this->resolveView($band->view), [
                'ctx' => $context,
                'record' => $context->record,
                'report' => $context->report,
            ]);
        }

        return '';
    }

    /**
     * @param  iterable<mixed>  $records
     * @return list<mixed>
     */
    private function normalize(iterable $records): array
    {
        if (is_array($records)) {
            return array_values($records);
        }

        $normalized = [];

        foreach ($records as $record) {
            $normalized[] = $record;
        }

        return $normalized;
    }

    private function renderLayout(ReportDefinition $definition, string $content, RenderContext $context): string
    {
        return $this->renderView($this->resolveLayoutView($definition->layout), [
            'content' => $content,
            'ctx' => $context,
            'title' => $context->report->name(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function renderView(string $name, array $data): string
    {
        $path = $this->views->getFinder()->find($name);

        return $this->views->file($path, $data)->render();
    }

    private function resolveView(string $view): string
    {
        if ($this->views->exists($view) || str_contains($view, '::')) {
            return $view;
        }

        return 'reports::'.$view;
    }

    private function resolveLayoutView(string $layout): string
    {
        if ($this->views->exists($layout) || str_contains($layout, '::')) {
            return $layout;
        }

        return 'reports::layouts.'.$layout;
    }
}
