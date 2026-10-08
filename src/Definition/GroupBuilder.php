<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition;

use Closure;
use Guggach\Reports\Definition\Bands\GroupFooter;
use Guggach\Reports\Definition\Bands\GroupHeader;

/**
 * Fluent builder for a single group level. Nested groups create deeper levels;
 * the ReportBuilder flattens them into an ordered GroupDefinition list.
 */
final class GroupBuilder
{
    /** @var list<string> */
    private readonly array $keys;

    private ?GroupHeader $header = null;

    private ?GroupFooter $footer = null;

    /** @var list<array<string, mixed>> */
    private array $aggregates = [];

    private ?Closure $localeUsing = null;

    private ?string $enrichment = null;

    private ?string $drivingSource = null;

    /** @var array<string, string> */
    private array $link = [];

    /** @var list<self> */
    private array $children = [];

    /**
     * @param  string|list<string>  $key
     */
    public function __construct(
        string|array $key,
        private readonly int $level,
    ) {
        $this->keys = is_array($key) ? $key : [$key];
    }

    public function header(GroupHeader|string|null $view = null, ?Closure $closure = null): self
    {
        if ($view instanceof GroupHeader) {
            if ($closure instanceof Closure) {
                $view->closure($closure);
            }

            $this->header = $view;

            return $this;
        }

        $this->header = new GroupHeader($view, $closure);

        return $this;
    }

    public function footer(GroupFooter|string|null $view = null, ?Closure $closure = null): self
    {
        if ($view instanceof GroupFooter) {
            if ($closure instanceof Closure) {
                $view->closure($closure);
            }

            $this->footer = $view;

            return $this;
        }

        $this->footer = new GroupFooter($view, $closure);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $aggregate
     */
    public function aggregate(array $aggregate): self
    {
        $this->aggregates[] = $aggregate;

        return $this;
    }

    public function aggSum(string $field, ?string $as = null): self
    {
        return $this->aggregate(['type' => 'sum', 'field' => $field, 'as' => $as ?? $field.'_sum']);
    }

    public function sum(string $field, ?string $as = null): self
    {
        return $this->aggSum($field, $as);
    }

    public function localeUsing(Closure|string|null $resolver): self
    {
        $this->localeUsing = $resolver instanceof Closure ? $resolver : null;

        return $this;
    }

    /**
     * Enrichment source: executed once per group (run-based), does not drive
     * the loop. See docs/spec.md 7.4.
     */
    public function enrichWith(string $source): self
    {
        $this->enrichment = $source;

        return $this;
    }

    /**
     * Driving source (top-down): the source drives the iteration.
     * See docs/spec.md 7.4.
     */
    public function drivesWith(string $source): self
    {
        $this->drivingSource = $source;

        return $this;
    }

    /**
     * @param  array<string, string>  $map
     */
    public function link(array $map): self
    {
        $this->link = $map;

        return $this;
    }

    /**
     * @param  string|list<string>  $key
     */
    public function group(string|array $key, ?Closure $callback = null): self
    {
        $child = new self($key, $this->level + 1);

        if ($callback instanceof Closure) {
            $callback($child);
        }

        $this->children[] = $child;

        return $child;
    }

    /**
     * Flatten this group and its children into an ordered list (pre-order),
     * so the position in the list equals the level.
     *
     * @return list<GroupDefinition>
     */
    public function build(): array
    {
        $groups = [
            new GroupDefinition(
                level: $this->level,
                keys: $this->keys,
                header: $this->header,
                footer: $this->footer,
                aggregates: $this->aggregates,
                localeUsing: $this->localeUsing,
                enrichment: $this->enrichment,
                drivingSource: $this->drivingSource,
                link: $this->link,
            ),
        ];

        foreach ($this->children as $child) {
            $groups = array_merge($groups, $child->build());
        }

        return $groups;
    }
}
