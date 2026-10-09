<?php

declare(strict_types=1);

namespace Guggach\Reports\Engine;

use Guggach\Reports\Definition\Bands\Band;
use Guggach\Reports\Definition\ReportDefinition;

/**
 * Flow mode assembler: emits one continuous document and lets Chromium
 * paginate. This is where the record loop lives: the detail band is rendered
 * once per record, group headers/footers are emitted on run changes, and
 * aggregates are accumulated per open group and for the report.
 */
final readonly class FlowPaginator
{
    public function __construct(
        private BandRenderer $bands,
    ) {}

    /**
     * @param  list<mixed>  $records
     */
    public function assemble(ReportDefinition $definition, array $records, RenderContext $context): string
    {
        $html = '';

        $html .= $this->render($definition->pageHeader, $context);
        $html .= $this->render($definition->reportStart, $context);
        $html .= $this->render($definition->gridHeader(), $context);

        $reportResolver = (new AggregateResolver)->bindReportSpecs($definition->aggregates);
        $groupResolver = new GroupResolver($definition->groups);

        $html .= $this->renderBody($definition, $records, $context, $groupResolver, $reportResolver);

        $html .= $this->render($definition->reportEnd, $context->withAggregates($reportResolver->values()));
        $html .= $this->render($definition->pageFooter, $context);

        return $html;
    }

    /**
     * @param  list<mixed>  $records
     */
    private function renderBody(
        ReportDefinition $definition,
        array $records,
        RenderContext $context,
        GroupResolver $groupResolver,
        AggregateResolver $reportResolver,
    ): string {
        $html = '';
        $detail = $definition->detail;
        $groups = $groupResolver->groups();
        $levels = count($groups);

        /** @var array<int, array{keys: list<string>, key: string, resolver: AggregateResolver}> $open */
        $open = [];
        $previousPath = [];

        foreach ($records as $index => $record) {
            $path = $groupResolver->path($record);
            $index = (int) $index;

            $changed = $this->firstChangedLevel($previousPath, $path, $levels);

            for ($level = $levels - 1; $level >= $changed; $level--) {
                if (! isset($open[$level])) {
                    continue;
                }

                $ctx = $context->withRecord($record, $index)
                    ->withGroups($this->openGroups($open))
                    ->withAggregates(array_merge($reportResolver->values(), $open[$level]['resolver']->values()));

                $html .= $this->render($groups[$level]->footer, $ctx);
                unset($open[$level]);
            }

            for ($level = $changed; $level < $levels; $level++) {
                $resolver = (new AggregateResolver)->bindGroupSpecs($groups[$level]->aggregates);
                $open[$level] = ['keys' => $groups[$level]->keys, 'key' => $path[$level], 'resolver' => $resolver];

                $ctx = $context->withRecord($record, $index)
                    ->withGroups($this->openGroups($open))
                    ->withAggregates(array_merge($reportResolver->values(), $resolver->values()));

                $html .= $this->render($groups[$level]->header, $ctx);
            }

            $reportResolver->add($record);

            foreach ($open as $entry) {
                $entry['resolver']->add($record);
            }

            if ($detail instanceof Band) {
                $ctx = $context->withRecord($record, $index)
                    ->withGroups($this->openGroups($open))
                    ->withAggregates(array_merge($reportResolver->values(), $this->deepestAggregates($open)));

                $html .= $this->render($detail, $ctx);
            }

            $previousPath = $path;
        }

        for ($level = $levels - 1; $level >= 0; $level--) {
            if (! isset($open[$level])) {
                continue;
            }

            $ctx = $context
                ->withGroups($this->openGroups($open))
                ->withAggregates(array_merge($reportResolver->values(), $open[$level]['resolver']->values()));

            $html .= $this->render($groups[$level]->footer, $ctx);
            unset($open[$level]);
        }

        return $html;
    }

    /**
     * @param  list<string>  $previous
     * @param  list<string>  $current
     */
    private function firstChangedLevel(array $previous, array $current, int $levels): int
    {
        for ($level = 0; $level < $levels; $level++) {
            if (! isset($previous[$level]) || $previous[$level] !== $current[$level]) {
                return $level;
            }
        }

        return $levels;
    }

    /**
     * @param  array<int, array{keys: list<string>, key: string, resolver: AggregateResolver}>  $open
     * @return list<array{level: int, keys: list<string>, key: string}>
     */
    private function openGroups(array $open): array
    {
        $groups = [];

        foreach ($open as $level => $entry) {
            $groups[] = ['level' => $level + 1, 'keys' => $entry['keys'], 'key' => $entry['key']];
        }

        return $groups;
    }

    /**
     * @param  array<int, array{keys: list<string>, key: string, resolver: AggregateResolver}>  $open
     * @return array<string, mixed>
     */
    private function deepestAggregates(array $open): array
    {
        if ($open === []) {
            return [];
        }

        return $open[array_key_last($open)]['resolver']->values();
    }

    private function render(?Band $band, RenderContext $context): string
    {
        if (! $band instanceof Band) {
            return '';
        }

        return $this->bands->renderBand($band, $context);
    }
}
