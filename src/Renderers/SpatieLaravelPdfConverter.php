<?php

declare(strict_types=1);

namespace Guggach\Reports\Renderers;

use Guggach\Reports\Contracts\HtmlToPdf;
use Guggach\Reports\Output\PdfOptions;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * Optional HtmlToPdf adapter for spatie/laravel-pdf. The concrete backend
 * (browsershot, chrome, gotenberg, cloudflare, weasyprint, ...) is chosen in
 * the spatie config (config('laravel-pdf.driver')).
 *
 * Requires spatie/laravel-pdf in the host application; the package only
 * suggests it.
 */
final class SpatieLaravelPdfConverter implements HtmlToPdf
{
    public function convert(string $html, PdfOptions $options): string
    {
        $builder = Pdf::html($html)
            ->format(mb_strtolower($options->format));

        $builder = $options->landscape ? $builder->landscape() : $builder->portrait();

        $margins = $options->margins;
        $builder->margins(
            $margins['top'],
            $margins['right'],
            $margins['bottom'],
            $margins['left'],
            'mm',
        );

        return $builder->generatePdfContent();
    }
}
