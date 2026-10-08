<?php

declare(strict_types=1);

namespace Guggach\Reports\Facades;

use Guggach\Reports\Definition\ReportDefinition;
use Guggach\Reports\Report;
use Guggach\Reports\Reports as ReportsManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed render(Report $report, array<string, mixed> $filters = [], array<string, mixed> $options = [])
 * @method static string html(Report $report, array<string, mixed> $filters = [], array<string, mixed> $options = [])
 * @method static ReportDefinition definition(Report $report)
 *
 * @see ReportsManager
 */
final class Reports extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ReportsManager::class;
    }
}
