<?php

declare(strict_types=1);

namespace Guggach\Reports\Definition;

final readonly class PageSetup
{
    /**
     * @param  array{top: int, right: int, bottom: int, left: int}  $margins
     */
    public function __construct(
        public string $size = 'a4',
        public string $orientation = 'portrait',
        public string $unit = 'mm',
        public array $margins = ['top' => 20, 'right' => 15, 'bottom' => 20, 'left' => 15],
        public int $dpi = 96,
    ) {}

    public static function a4(): self
    {
        return new self;
    }

    public function portrait(): self
    {
        return $this->copy(orientation: 'portrait');
    }

    public function landscape(): self
    {
        return $this->copy(orientation: 'landscape');
    }

    public function marginsMm(int $top, int $right, int $bottom, int $left): self
    {
        return $this->copy(
            unit: 'mm',
            margins: ['top' => $top, 'right' => $right, 'bottom' => $bottom, 'left' => $left],
        );
    }

    /**
     * @param  array{top: int, right: int, bottom: int, left: int}|null  $margins
     */
    private function copy(
        ?string $size = null,
        ?string $orientation = null,
        ?string $unit = null,
        ?array $margins = null,
        ?int $dpi = null,
    ): self {
        return new self(
            size: $size ?? $this->size,
            orientation: $orientation ?? $this->orientation,
            unit: $unit ?? $this->unit,
            margins: $margins ?? $this->margins,
            dpi: $dpi ?? $this->dpi,
        );
    }
}
