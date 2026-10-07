<?php

declare(strict_types=1);

namespace Guggach\Reports\Contracts;

use Guggach\Reports\Report;

interface ReportRenderer
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function render(Report $report, array $options = []): mixed;

    public function format(): string;
}
