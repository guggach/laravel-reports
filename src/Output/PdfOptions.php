<?php

declare(strict_types=1);

namespace Guggach\Reports\Output;

use Guggach\Reports\Definition\PageSetup;

/**
 * Options for HTML -> PDF conversion, derived from the effective PageSetup.
 */
final readonly class PdfOptions
{
    /**
     * @param  array{top: int, right: int, bottom: int, left: int}  $margins  in mm
     */
    public function __construct(
        public string $format = 'A4',
        public bool $landscape = false,
        public array $margins = ['top' => 20, 'right' => 15, 'bottom' => 20, 'left' => 15],
        public bool $printBackground = true,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public static function fromPageSetup(PageSetup $pageSetup, array $options = []): self
    {
        $printBackground = $options['print_background']
            ?? config('reports.pdf.options.print_background', true);

        return new self(
            format: mb_strtoupper($pageSetup->size),
            landscape: $pageSetup->orientation === 'landscape',
            margins: $pageSetup->margins,
            printBackground: is_bool($printBackground) ? $printBackground : true,
        );
    }
}
