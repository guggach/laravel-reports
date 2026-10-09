<?php

declare(strict_types=1);

use Guggach\Reports\Contracts\HtmlToPdf;
use Guggach\Reports\Reports;
use Guggach\Reports\Tests\Fixtures\CompanyLayout;
use Guggach\Reports\Tests\Fixtures\DemoReport;
use Guggach\Reports\Tests\Fixtures\RecordingHtmlToPdf;

it('converts the rendered html to pdf via the bound converter', function (): void {
    $converter = new RecordingHtmlToPdf;
    app()->instance(HtmlToPdf::class, $converter);

    $report = new DemoReport([['name' => 'A']], layout: CompanyLayout::class);

    $bytes = app(Reports::class)->pdf($report);

    expect($bytes)->toStartWith('%PDF')
        ->and($converter->html)->toContain('<p>A</p>')
        ->and($converter->options?->format)->toBe('A4')
        ->and($converter->options?->landscape)->toBeTrue()
        ->and($converter->options?->margins['top'])->toBe(20);
});

it('stores the pdf to a file', function (): void {
    app()->instance(HtmlToPdf::class, new RecordingHtmlToPdf);

    $path = sys_get_temp_dir().'/reports-test-'.uniqid().'.pdf';

    $stored = app(Reports::class)->store(new DemoReport([['name' => 'A']]), $path);

    expect($stored)->toBe($path)
        ->and(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toStartWith('%PDF');

    @unlink($path);
});

it('fails with a helpful message when no converter is bound', function (): void {
    expect(fn () => app(Reports::class)->pdf(new DemoReport([['name' => 'A']])))
        ->toThrow(RuntimeException::class, 'No HTML-to-PDF converter is bound');
});
