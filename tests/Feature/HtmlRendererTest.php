<?php

declare(strict_types=1);

use Guggach\Reports\Definition\ReportBuilder;
use Guggach\Reports\Report;
use Guggach\Reports\Reports;
use Guggach\Reports\Sources\ArraySource;

/**
 * @param  list<array<string, mixed>>  $records
 */
function makeHtmlReport(array $records, ?Closure $configure = null): Report
{
    return new class($records, $configure) extends Report
    {
        /**
         * @param  list<array<string, mixed>>  $records
         */
        public function __construct(
            private readonly array $records,
            private readonly ?Closure $configure,
        ) {}

        public function key(): string
        {
            return 'demo';
        }

        public function name(): string
        {
            return 'Demo Report';
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
    };
}

it('renders the detail band once per record, in order', function (): void {
    $html = app(Reports::class)->html(makeHtmlReport([
        ['name' => 'A'],
        ['name' => 'B'],
        ['name' => 'C'],
    ]));

    expect($html)->toContain('<p>A</p>')
        ->toContain('<p>B</p>')
        ->toContain('<p>C</p>');

    expect(mb_strpos($html, '<p>A</p>'))
        ->toBeLessThan(mb_strpos($html, '<p>B</p>'))
        ->toBeLessThan(mb_strpos($html, '<p>C</p>'));
});

it('wraps the bands in the report layout', function (): void {
    $html = app(Reports::class)->html(makeHtmlReport([['name' => 'A']]));

    expect($html)->toContain('<!DOCTYPE html>')
        ->toContain('<title>Demo Report</title>')
        ->toContain('<p>A</p>');
});

it('renders the bands in stack order', function (): void {
    $report = makeHtmlReport([['name' => 'A']], function (ReportBuilder $r): void {
        $r->pageHeader(closure: fn (): string => 'HEADER');
        $r->reportStart(closure: fn (): string => 'START');
        $r->detail(closure: fn ($ctx): string => 'DETAIL:'.$ctx->name);
        $r->reportEnd(closure: fn (): string => 'END');
        $r->pageFooter(closure: fn (): string => 'FOOTER');
    });

    $html = app(Reports::class)->html($report);

    expect(mb_strpos($html, 'HEADER'))->toBeLessThan(mb_strpos($html, 'START'));
    expect(mb_strpos($html, 'START'))->toBeLessThan(mb_strpos($html, 'DETAIL:A'));
    expect(mb_strpos($html, 'DETAIL:A'))->toBeLessThan(mb_strpos($html, 'END'));
    expect(mb_strpos($html, 'END'))->toBeLessThan(mb_strpos($html, 'FOOTER'));
});

it('exposes index, total and position flags to the band', function (): void {
    $report = makeHtmlReport([['name' => 'A'], ['name' => 'B']], function (ReportBuilder $r): void {
        $r->detail(closure: fn ($ctx): string => sprintf(
            '[%d/%d:%s%s]',
            $ctx->index,
            $ctx->total,
            $ctx->isFirst() ? 'F' : '-',
            $ctx->isLast() ? 'L' : '-',
        ));
    });

    $html = app(Reports::class)->html($report);

    expect($html)->toContain('[0/2:F-]')->toContain('[1/2:-L]');
});

it('exposes the locale to the bands and restores it afterwards', function (): void {
    app()->setLocale('en');

    $report = makeHtmlReport([['name' => 'A']], function (ReportBuilder $r): void {
        $r->detail(closure: fn ($ctx): string => 'locale='.($ctx->locale ?? 'null'));
    });

    $html = app(Reports::class)->html($report, options: ['locale' => 'de']);

    expect($html)->toContain('locale=de');
    expect(app()->getLocale())->toBe('en');
});
