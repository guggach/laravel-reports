<?php

declare(strict_types=1);

namespace Guggach\Reports\Tests\Fixtures;

use Guggach\Reports\Layouts\Layout;

final class PlainLayout extends Layout
{
    public function view(): string
    {
        return 'reports::layouts.letterhead';
    }
}
