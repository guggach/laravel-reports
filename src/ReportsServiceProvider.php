<?php

declare(strict_types=1);

namespace Guggach\Reports;

use Guggach\Reports\Contracts\HtmlToPdf;
use Guggach\Reports\Contracts\ReportRenderer;
use Guggach\Reports\Engine\ReportEngine;
use Guggach\Reports\Renderers\HtmlRenderer;
use Guggach\Reports\Renderers\UnconfiguredHtmlToPdf;
use Illuminate\Support\ServiceProvider;

final class ReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/reports.php', 'reports');

        $this->app->bindIf(HtmlToPdf::class, UnconfiguredHtmlToPdf::class);
        $this->app->singleton(ReportRenderer::class, HtmlRenderer::class);
        $this->app->singleton(ReportEngine::class);
        $this->app->singleton(Reports::class);
        $this->app->alias(Reports::class, 'reports');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'reports');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/reports.php' => config_path('reports.php'),
            ], 'reports-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'reports-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/reports'),
            ], 'reports-views');
        }
    }
}
