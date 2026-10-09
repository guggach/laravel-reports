<?php

declare(strict_types=1);

use Guggach\Reports\Definition\PageSetup;
use Guggach\Reports\Reports;
use Guggach\Reports\Tests\Fixtures\CompanyLayout;
use Guggach\Reports\Tests\Fixtures\DemoReport;

it('exposes the effective page setup through the manager', function (): void {
    $report = new DemoReport([['name' => 'A']], layout: CompanyLayout::class);

    $pageSetup = app(Reports::class)->pageSetup($report);

    expect($pageSetup->size)->toBe('a4')
        ->and($pageSetup->orientation)->toBe('landscape');
});

it('renders the preview frame with an iframe and paper geometry', function (): void {
    $html = view('reports::components.frame', [
        'url' => '/report/price-list',
        'pageSetup' => PageSetup::a4()->portrait(),
        'title' => 'Preisliste',
    ])->render();

    expect($html)->toContain('class="report-frame"')
        ->toContain('src="/report/price-list"')
        ->toContain('data-page-size="a4"')
        ->toContain('data-page-orientation="portrait"')
        ->toContain('width: 210mm')
        ->toContain('Drucken');
});

it('uses the paper width for landscape orientation', function (): void {
    $html = view('reports::components.frame', [
        'url' => '/report/price-list',
        'pageSetup' => PageSetup::a4()->landscape(),
    ])->render();

    expect($html)->toContain('width: 297mm')
        ->toContain('data-page-orientation="landscape"');
});

it('renders an optional pdf action', function (): void {
    $html = view('reports::components.frame', [
        'url' => '/report/price-list',
        'pageSetup' => PageSetup::a4(),
        'pdf' => '/report/price-list.pdf',
    ])->render();

    expect($html)->toContain('href="/report/price-list.pdf"');
});
