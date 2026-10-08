<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition;

use Closure;
use Guggach\Reports\Definition\Bands\Band;
use Guggach\Reports\Definition\Bands\Detail;
use Guggach\Reports\Definition\Bands\GridHeader;
use Guggach\Reports\Definition\Bands\PageFooter;
use Guggach\Reports\Definition\Bands\PageHeader;
use Guggach\Reports\Definition\Bands\ReportEnd;
use Guggach\Reports\Definition\Bands\ReportStart;

final class ReportBuilder
{
    private ReportMode $mode = ReportMode::Flow;

    private PageSetup $pageSetup;

    private string $layout = 'default';

    /** @var list<array<string, mixed>> */
    private array $filters = [];

    /** @var list<GroupBuilder> */
    private array $groupBuilders = [];

    /** @var list<array<string, mixed>> */
    private array $aggregates = [];

    private ?Detail $detail = null;

    private ?PageHeader $pageHeader = null;

    private ?PageFooter $pageFooter = null;

    private ?ReportStart $reportStart = null;

    private ?ReportEnd $reportEnd = null;

    private ?GridHeader $gridHeader = null;

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

    public function detail(Detail|string|null $view = null, ?Closure $closure = null, ?float $height = null): Detail
    {
        if ($view instanceof Detail) {
            $this->applyOverride($view, $closure, $height);

            return $this->detail = $view;
        }

        return $this->detail = new Detail($view, $closure, $height);
    }

    public function pageHeader(PageHeader|string|null $view = null, ?Closure $closure = null): PageHeader
    {
        if ($view instanceof PageHeader) {
            return $this->pageHeader = $this->applyOverride($view, $closure);
        }

        return $this->pageHeader = new PageHeader($view, $closure);
    }

    public function pageFooter(PageFooter|string|null $view = null, ?Closure $closure = null): PageFooter
    {
        if ($view instanceof PageFooter) {
            return $this->pageFooter = $this->applyOverride($view, $closure);
        }

        return $this->pageFooter = new PageFooter($view, $closure);
    }

    public function reportStart(ReportStart|string|null $view = null, ?Closure $closure = null): ReportStart
    {
        if ($view instanceof ReportStart) {
            return $this->reportStart = $this->applyOverride($view, $closure);
        }

        return $this->reportStart = new ReportStart($view, $closure);
    }

    public function reportEnd(ReportEnd|string|null $view = null, ?Closure $closure = null): ReportEnd
    {
        if ($view instanceof ReportEnd) {
            return $this->reportEnd = $this->applyOverride($view, $closure);
        }

        return $this->reportEnd = new ReportEnd($view, $closure);
    }

    public function gridHeader(GridHeader|string|null $view = null, ?Closure $closure = null): GridHeader
    {
        if ($view instanceof GridHeader) {
            return $this->gridHeader = $this->applyOverride($view, $closure);
        }

        return $this->gridHeader = new GridHeader($view, $closure);
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
     * @param  string|list<string>  $key
     */
    public function group(string|array $key, ?Closure $callback = null): GroupBuilder
    {
        $group = new GroupBuilder($key, 1);

        if ($callback instanceof Closure) {
            $callback($group);
        }

        $this->groupBuilders[] = $group;

        return $group;
    }

    public function aggregate(string $name, string $class, ?string $field = null, string $scope = 'report'): self
    {
        $this->aggregates[] = [
            'name' => $name,
            'class' => $class,
            'field' => $field,
            'scope' => $scope,
        ];

        return $this;
    }

    public function build(): ReportDefinition
    {
        $groups = [];

        foreach ($this->groupBuilders as $groupBuilder) {
            $groups = array_merge($groups, $groupBuilder->build());
        }

        return new ReportDefinition(
            mode: $this->mode,
            layout: $this->layout,
            pageSetup: $this->pageSetup,
            filters: $this->filters,
            groups: $groups,
            aggregates: $this->aggregates,
            detail: $this->detail,
            pageHeader: $this->pageHeader,
            pageFooter: $this->pageFooter,
            reportStart: $this->reportStart,
            reportEnd: $this->reportEnd,
            gridHeader: $this->gridHeader,
        );
    }

    /**
     * Apply an optional closure/height onto a caller-supplied band instance
     * (variant A: pass a prepared band instead of a view name).
     *
     * @template TBand of Band
     *
     * @param  TBand  $band
     * @return TBand
     */
    private function applyOverride(Band $band, ?Closure $closure = null, ?float $height = null): Band
    {
        if ($closure instanceof Closure) {
            $band->closure($closure);
        }

        if ($height !== null) {
            $band->height($height);
        }

        return $band;
    }
}
