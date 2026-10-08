<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition;

use Closure;

/**
 * One group level. Levels are stored as an ordered list in the
 * ReportDefinition; the position defines the level (index 0 => level 1).
 */
final readonly class GroupDefinition
{
    /**
     * @param  list<string>  $keys
     * @param  list<array<string, mixed>>  $aggregates
     * @param  array<string, string>  $link
     */
    public function __construct(
        public int $level,
        public array $keys,
        public ?Bands\GroupHeader $header = null,
        public ?Bands\GroupFooter $footer = null,
        public array $aggregates = [],
        public ?Closure $localeUsing = null,
        public ?string $enrichment = null,
        public ?string $drivingSource = null,
        public array $link = [],
    ) {}
}
