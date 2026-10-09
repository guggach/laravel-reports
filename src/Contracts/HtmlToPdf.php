<?php

declare(strict_types=1);

namespace Guggach\Reports\Contracts;

use Guggach\Reports\Output\PdfOptions;

/**
 * Converts rendered report HTML into PDF bytes. The implementation is provided
 * by the host application (e.g. spatie/laravel-pdf, Browsershot or a direct
 * Chromium/Playwright call) so the package stays free of a hard browser
 * dependency.
 */
interface HtmlToPdf
{
    /**
     * @return string raw PDF bytes
     */
    public function convert(string $html, PdfOptions $options): string;
}
