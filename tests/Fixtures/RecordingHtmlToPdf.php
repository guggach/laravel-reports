<?php

declare(strict_types=1);

namespace Guggach\Reports\Tests\Fixtures;

use Guggach\Reports\Contracts\HtmlToPdf;
use Guggach\Reports\Output\PdfOptions;

final class RecordingHtmlToPdf implements HtmlToPdf
{
    public ?string $html = null;

    public ?PdfOptions $options = null;

    public function convert(string $html, PdfOptions $options): string
    {
        $this->html = $html;
        $this->options = $options;

        return '%PDF-1.4 fake report';
    }
}
