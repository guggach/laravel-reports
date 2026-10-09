<?php

declare(strict_types=1);

use Guggach\Reports\Definition\ReportBuilder;
use Guggach\Reports\Reports;
use Guggach\Reports\Tests\Fixtures\CompanyLayout;
use Guggach\Reports\Tests\Fixtures\DemoReport;
use Guggach\Reports\Tests\Fixtures\PlainLayout;

it('renders the detail band once per record, in order', function (): void {
    $html = app(Reports::class)->html(new DemoReport([
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
    $html = app(Reports::class)->html(new DemoReport([['name' => 'A']]));

    expect($html)->toContain('<!DOCTYPE html>')
        ->toContain('<title>Demo Report</title>')
        ->toContain('<p>A</p>');
});

it('renders the bands in stack order', function (): void {
    $report = new DemoReport([['name' => 'A']], function (ReportBuilder $r): void {
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
    $report = new DemoReport([['name' => 'A'], ['name' => 'B']], function (ReportBuilder $r): void {
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

    $report = new DemoReport([['name' => 'A']], function (ReportBuilder $r): void {
        $r->detail(closure: fn ($ctx): string => 'locale='.($ctx->locale ?? 'null'));
    });

    $html = app(Reports::class)->html($report, options: ['locale' => 'de']);

    expect($html)->toContain('locale=de');
    expect(app()->getLocale())->toBe('en');
});

it('wraps the report in a Layout class and passes slots and page setup', function (): void {
    $report = new DemoReport([['name' => 'A']], layout: CompanyLayout::class);

    $html = app(Reports::class)->html($report, options: ['meta' => ['company' => 'Acme AG']]);

    expect($html)->toContain('data-layout="letterhead"')
        ->toContain('Acme AG')
        ->toContain('<p>A</p>');

    // Layout-level PageSetup wins over the config default.
    expect($html)->toContain('<meta name="page-size" content="a4-landscape">');
});

it('falls back to the config page setup when the layout defines none', function (): void {
    config(['reports.paper' => [
        'size' => 'a4',
        'orientation' => 'portrait',
        'unit' => 'mm',
        'margins' => ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10],
        'dpi' => 96,
    ]]);

    $report = new DemoReport([['name' => 'A']], layout: PlainLayout::class);

    $html = app(Reports::class)->html($report);

    expect($html)->toContain('<meta name="page-size" content="a4-portrait">')
        ->toContain('<p>A</p>');
});
