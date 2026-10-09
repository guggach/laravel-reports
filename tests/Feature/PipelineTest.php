<?php

declare(strict_types=1);

use Guggach\Reports\Aggregates\Sum;
use Guggach\Reports\Definition\ReportBuilder;
use Guggach\Reports\Reports;
use Guggach\Reports\Tests\Fixtures\DemoReport;

it('emits group headers/footers and aggregates in run order', function (): void {
    $records = [
        ['category' => 'A', 'name' => 'a1', 'price' => 10],
        ['category' => 'A', 'name' => 'a2', 'price' => 20],
        ['category' => 'B', 'name' => 'b1', 'price' => 5],
    ];

    $report = new DemoReport($records, function (ReportBuilder $r): void {
        $r->group('category', function ($g): void {
            $g->header(closure: fn ($ctx): string => '[H:'.$ctx->currentGroup()['key'].']');
            $g->footer(closure: fn ($ctx): string => '[F:'.$ctx->currentGroup()['key'].'='.$ctx->aggregates['cat_total'].']');
            $g->aggSum('price', as: 'cat_total');
        });

        $r->reportEnd(closure: fn ($ctx): string => '[TOTAL:'.$ctx->aggregates['grand_total'].']');
        $r->aggregate('grand_total', Sum::class, field: 'price', scope: 'report');
    });

    $html = app(Reports::class)->html($report);

    expect(mb_strpos($html, '[H:A]'))->toBeLessThan(mb_strpos($html, '<p>a1</p>'));
    expect(mb_strpos($html, '<p>a1</p>'))->toBeLessThan(mb_strpos($html, '<p>a2</p>'));
    expect(mb_strpos($html, '<p>a2</p>'))->toBeLessThan(mb_strpos($html, '[F:A=30]'));
    expect(mb_strpos($html, '[F:A=30]'))->toBeLessThan(mb_strpos($html, '[H:B]'));
    expect(mb_strpos($html, '[H:B]'))->toBeLessThan(mb_strpos($html, '<p>b1</p>'));
    expect(mb_strpos($html, '<p>b1</p>'))->toBeLessThan(mb_strpos($html, '[F:B=5]'));
    expect(mb_strpos($html, '[F:B=5]'))->toBeLessThan(mb_strpos($html, '[TOTAL:35]'));
});

it('nests group levels', function (): void {
    $records = [
        ['category' => 'A', 'brand' => 'X', 'name' => 'a1'],
        ['category' => 'A', 'brand' => 'Y', 'name' => 'a2'],
        ['category' => 'B', 'brand' => 'X', 'name' => 'b1'],
    ];

    $report = new DemoReport($records, function (ReportBuilder $r): void {
        $r->group('category', function ($g): void {
            $g->header(closure: fn ($ctx): string => '[C:'.$ctx->currentGroup()['key'].']');
            $g->group('brand', function ($g): void {
                $g->header(closure: fn ($ctx): string => '[B:'.$ctx->currentGroup()['key'].']');
                $g->footer(closure: fn ($ctx): string => '[/B:'.$ctx->currentGroup()['key'].']');
            });
        });
    });

    $html = app(Reports::class)->html($report);

    expect(mb_strpos($html, '[C:A]'))->toBeLessThan(mb_strpos($html, '[B:X]'));
    expect(mb_strpos($html, '[B:X]'))->toBeLessThan(mb_strpos($html, '<p>a1</p>'));
    expect(mb_strpos($html, '<p>a1</p>'))->toBeLessThan(mb_strpos($html, '[/B:X]'));
    expect(mb_strpos($html, '[/B:X]'))->toBeLessThan(mb_strpos($html, '[B:Y]'));
    expect(mb_strpos($html, '[B:Y]'))->toBeLessThan(mb_strpos($html, '<p>a2</p>'));
    expect(mb_strpos($html, '<p>a2</p>'))->toBeLessThan(mb_strpos($html, '[/B:Y]'));

    // Second category opens a fresh brand group.
    $categoryB = mb_strpos($html, '[C:B]');
    $brandXAfterB = mb_strpos($html, '[B:X]', $categoryB);

    expect($brandXAfterB)->toBeGreaterThan($categoryB);
    expect($brandXAfterB)->toBeLessThan(mb_strpos($html, '<p>b1</p>'));
});

it('renders details without group bands when no groups are defined', function (): void {
    $report = new DemoReport([['name' => 'x'], ['name' => 'y']]);

    $html = app(Reports::class)->html($report);

    expect($html)->toContain('<p>x</p>')->toContain('<p>y</p>');
});
