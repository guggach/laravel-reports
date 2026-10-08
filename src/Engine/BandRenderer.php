<?php

declare(strict_types=1);

namespace Guggach\Reports\Engine;

use Guggach\Reports\Definition\Bands\Band;

/**
 * Renders a single band into markup. The paginator uses this to render the
 * band stack; the concrete implementation decides Blade vs. closure.
 */
interface BandRenderer
{
    public function renderBand(Band $band, RenderContext $context): string;
}
