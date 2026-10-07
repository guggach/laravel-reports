<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition;

final readonly class ReportDefinition
{
    /**
     * @param  list<array<string, mixed>>  $filters
     * @param  list<array<string, mixed>>  $groups
     * @param  list<array<string, mixed>>  $aggregates
     */
    public function __construct(
        public ReportMode $mode = ReportMode::Flow,
        public string $layout = 'default',
        public PageSetup $pageSetup = new PageSetup,
        public array $filters = [],
        public array $groups = [],
        public array $aggregates = [],
    ) {}
}
