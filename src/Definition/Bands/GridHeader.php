<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition\Bands;

use Closure;

class GridHeader extends Band
{
    public function __construct(?string $view = null, ?Closure $closure = null, ?float $height = null)
    {
        parent::__construct($view, $closure, $height);

        $this->repeatOnNewPage = true;
    }
}
