<?php

declare(strict_types=1);

namespace Guggach\Reports\Renderers;

use Guggach\Reports\Contracts\HtmlToPdf;
use Guggach\Reports\Output\PdfOptions;
use RuntimeException;

/**
 * Default binding so the container can always resolve HtmlToPdf. It fails with
 * a helpful message until the host binds a real converter.
 */
final class UnconfiguredHtmlToPdf implements HtmlToPdf
{
    public function convert(string $html, PdfOptions $options): string
    {
        throw new RuntimeException(
            'No HTML-to-PDF converter is bound. Bind '.HtmlToPdf::class.' in the host '.
            'application (e.g. spatie/laravel-pdf, Browsershot or a Chromium call).'
        );
    }
}
