<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition\Bands;

use Closure;

/**
 * The central band: rendered once per detail record. Each run is a complete
 * unit (grid row, invoice form instance, ...).
 */
class Detail extends Band
{
    public ?GridHeader $gridHeader = null;

    public function __construct(?string $view = null, ?Closure $closure = null, ?float $height = null)
    {
        parent::__construct($view, $closure, $height);

        $this->keepTogether = true;
    }

    /**
     * A grid header that is repeated on every new page of a running detail
     * block (recommended as a real <thead> in Flow mode).
     */
    public function repeatGridHeader(?string $view = null, ?Closure $closure = null): static
    {
        $this->gridHeader = new GridHeader($view, $closure);

        return $this;
    }
}
