<?php

declare(strict_types=1);

namespace Guggach\Reports\Engine;

use Guggach\Reports\Definition\Bands\Band;
use Guggach\Reports\Definition\ReportDefinition;

/**
 * Flow mode assembler: emits one continuous document and lets Chromium
 * paginate. This is where the record loop lives: the detail band is rendered
 * once per record.
 *
 * Strict mode (arithmetic pagination) will be a sibling implementation behind
 * the same conceptual contract.
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

        $detail = $definition->detail;

        if ($detail instanceof \Guggach\Reports\Definition\Bands\Detail) {
            foreach ($records as $index => $record) {
                // Record for record: a fresh context per detail record.
                $html .= $this->render($detail, $context->withRecord($record, (int) $index));
            }
        }

        $html .= $this->render($definition->reportEnd, $context);
        $html .= $this->render($definition->pageFooter, $context);

        return $html;
    }

    private function render(?Band $band, RenderContext $context): string
    {
        if (! $band instanceof Band) {
            return '';
        }

        return $this->bands->renderBand($band, $context);
    }
}
