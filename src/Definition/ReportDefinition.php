<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition;

use Guggach\Reports\Definition\Bands\Detail;
use Guggach\Reports\Definition\Bands\GridHeader;
use Guggach\Reports\Definition\Bands\PageFooter;
use Guggach\Reports\Definition\Bands\PageHeader;
use Guggach\Reports\Definition\Bands\ReportEnd;
use Guggach\Reports\Definition\Bands\ReportStart;

final readonly class ReportDefinition
{
    /**
     * @param  list<array<string, mixed>>  $filters
     * @param  list<GroupDefinition>  $groups
     * @param  list<array<string, mixed>>  $aggregates
     */
    public function __construct(
        public ReportMode $mode = ReportMode::Flow,
        public string $layout = 'default',
        public PageSetup $pageSetup = new PageSetup,
        public array $filters = [],
        public array $groups = [],
        public array $aggregates = [],
        public ?Detail $detail = null,
        public ?PageHeader $pageHeader = null,
        public ?PageFooter $pageFooter = null,
        public ?ReportStart $reportStart = null,
        public ?ReportEnd $reportEnd = null,
        public ?GridHeader $gridHeader = null,
    ) {}

    /**
     * The grid header to repeat on every page: either set on the detail band
     * (repeatGridHeader) or directly on the builder (gridHeader).
     */
    public function gridHeader(): ?GridHeader
    {
        $detail = $this->detail;

        if ($detail instanceof Detail && $detail->gridHeader instanceof GridHeader) {
            return $detail->gridHeader;
        }

        return $this->gridHeader;
    }
}
