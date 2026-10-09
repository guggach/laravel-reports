<?php

declare(strict_types=1);

namespace Guggach\Reports\Tests\Fixtures;

use Closure;
use Guggach\Reports\Definition\ReportBuilder;
use Guggach\Reports\Report;
use Guggach\Reports\Sources\ArraySource;

final class DemoReport extends Report
{
    /**
     * @param  list<array<string, mixed>>  $records
     */
    public function __construct(
        private readonly array $records = [],
        private readonly ?Closure $configure = null,
        private readonly ?string $layout = null,
    ) {}

    public function key(): string
    {
        return 'demo';
    }

    public function name(): string
    {
        return 'Demo Report';
    }

    public function layout(): string
    {
        return $this->layout ?? 'default';
    }

    public function source(): ArraySource
    {
        return new ArraySource($this->records);
    }

    public function define(ReportBuilder $r): void
    {
        $r->detail(closure: fn ($ctx): string => '<p>'.$ctx->name.'</p>');

        if ($this->configure instanceof Closure) {
            ($this->configure)($r);
        }
    }
}
