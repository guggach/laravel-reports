<?php

declare(strict_types=1);

namespace Guggach\Reports\Tests\Fixtures;

use Guggach\Reports\Definition\PageSetup;
use Guggach\Reports\Layouts\Layout;

final class CompanyLayout extends Layout
{
    public function view(): string
    {
        return 'reports::layouts.letterhead';
    }

    public function pageSetup(): PageSetup
    {
        return PageSetup::a4()->landscape();
    }
}
