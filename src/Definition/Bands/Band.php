<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition\Bands;

use Closure;

/**
 * A band is a full-width block with an optional fixed height and break rules.
 * It is rendered either from a Blade view or from a closure that receives the
 * RenderContext.
 */
abstract class Band
{
    public ?float $minHeight = null;

    public bool $keepTogether = false;

    public bool $breakBefore = false;

    public bool $breakAfter = false;

    public bool $repeatOnNewPage = false;

    /** @var list<string> */
    public array $visibilities = [];

    /** @var list<int>|null */
    public ?array $pages = null;

    public Closure|string|null $localeUsing = null;

    public function __construct(public ?string $view = null, public ?Closure $closure = null, public ?float $height = null) {}

    public function view(?string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function closure(?Closure $closure): static
    {
        $this->closure = $closure;

        return $this;
    }

    public function height(?float $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function minHeight(?float $minHeight): static
    {
        $this->minHeight = $minHeight;

        return $this;
    }

    public function keepTogether(bool $keepTogether = true): static
    {
        $this->keepTogether = $keepTogether;

        return $this;
    }

    public function breakBefore(bool $breakBefore = true): static
    {
        $this->breakBefore = $breakBefore;

        return $this;
    }

    public function breakAfter(bool $breakAfter = true): static
    {
        $this->breakAfter = $breakAfter;

        return $this;
    }

    public function repeatOnNewPage(bool $repeatOnNewPage = true): static
    {
        $this->repeatOnNewPage = $repeatOnNewPage;

        return $this;
    }

    public function hideOnFirstPage(): static
    {
        $this->visibilities[] = 'hideOnFirstPage';

        return $this;
    }

    public function hideOnLastPage(): static
    {
        $this->visibilities[] = 'hideOnLastPage';

        return $this;
    }

    public function onlyOddPages(): static
    {
        $this->visibilities[] = 'onlyOddPages';

        return $this;
    }

    public function onlyEvenPages(): static
    {
        $this->visibilities[] = 'onlyEvenPages';

        return $this;
    }

    /**
     * @param  list<int>  $pages
     */
    public function pages(array $pages): static
    {
        $this->pages = $pages;

        return $this;
    }

    public function localeUsing(Closure|string|null $resolver): static
    {
        $this->localeUsing = $resolver;

        return $this;
    }
}
