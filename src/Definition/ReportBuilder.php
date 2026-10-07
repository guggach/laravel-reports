<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition;

final class ReportBuilder
{
    private ReportMode $mode = ReportMode::Flow;

    private PageSetup $pageSetup;

    private string $layout = 'default';

    /** @var list<array<string, mixed>> */
    private array $filters = [];

    /** @var list<array<string, mixed>> */
    private array $groups = [];

    /** @var list<array<string, mixed>> */
    private array $aggregates = [];

    public function __construct()
    {
        $this->pageSetup = PageSetup::a4();
    }

    public function mode(ReportMode $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function pageSetup(PageSetup $pageSetup): self
    {
        $this->pageSetup = $pageSetup;

        return $this;
    }

    public function layout(string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    public function filter(array $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $group
     */
    public function group(array $group): self
    {
        $this->groups[] = $group;

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

    public function build(): ReportDefinition
    {
        return new ReportDefinition(
            mode: $this->mode,
            layout: $this->layout,
            pageSetup: $this->pageSetup,
            filters: $this->filters,
            groups: $this->groups,
            aggregates: $this->aggregates,
        );
    }
}
