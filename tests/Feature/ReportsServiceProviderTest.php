<?php

declare(strict_types=1);

use Guggach\Reports\Definition\ReportBuilder;
use Guggach\Reports\Definition\ReportMode;
use Guggach\Reports\Report;
use Guggach\Reports\Reports;
use Guggach\Reports\Sources\ArraySource;
use Illuminate\Support\Facades\Schema;

it('resolves the reports manager from the container', function (): void {
    expect(app(Reports::class))->toBeInstanceOf(Reports::class);
});

it('merges the package config', function (): void {
    expect(config('reports.default_mode'))->toBe('flow');
    expect(config('reports.paper.size'))->toBe('a4');
});

it('builds a report definition from a report class', function (): void {
    $report = new class extends Report
    {
        public function key(): string
        {
            return 'demo';
        }

        public function name(): string
        {
            return 'Demo';
        }

        public function source(): ArraySource
        {
            return new ArraySource;
        }

        public function define(ReportBuilder $builder): void
        {
            $builder->mode(ReportMode::Strict);
        }
    };

    expect($report->definition()->mode)->toBe(ReportMode::Strict);
});

it('registers the package migrations', function (): void {
    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasTable('report_presets'))->toBeTrue();
    expect(Schema::hasTable('report_outputs'))->toBeTrue();
});
