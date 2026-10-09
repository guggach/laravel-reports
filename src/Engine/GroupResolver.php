<?php

declare(strict_types=1);

namespace Guggach\Reports\Engine;

use Guggach\Reports\Definition\GroupDefinition;
use Guggach\Reports\Support\Record;

/**
 * Run-based grouping: computes the composite key per group level for a record,
 * so the paginator can detect when a group starts or ends.
 */
final readonly class GroupResolver
{
    /**
     * @param  list<GroupDefinition>  $groups
     */
    public function __construct(
        private array $groups,
    ) {}

    /**
     * @return list<GroupDefinition>
     */
    public function groups(): array
    {
        return $this->groups;
    }

    public function levelCount(): int
    {
        return count($this->groups);
    }

    /**
     * Composite key per level for the given record.
     *
     * @return list<string>
     */
    public function path(mixed $record): array
    {
        $path = [];

        foreach ($this->groups as $group) {
            $parts = [];

            foreach ($group->keys as $key) {
                $parts[] = Record::string($record, $key);
            }

            $path[] = implode("\x1f", $parts);
        }

        return $path;
    }
}
