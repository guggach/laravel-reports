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

    /**
     * Build a PageSetup from the package config (reports.paper). Used as the
     * lowest layer of the Config -> Layout -> Report cascade.
     */
    public static function fromConfig(mixed $config): self
    {
        $config = is_array($config) ? $config : [];

        $margins = $config['margins'] ?? null;
        $margins = is_array($margins) ? $margins : [];

        return new self(
            size: is_string($config['size'] ?? null) ? $config['size'] : 'a4',
            orientation: is_string($config['orientation'] ?? null) ? $config['orientation'] : 'portrait',
            unit: is_string($config['unit'] ?? null) ? $config['unit'] : 'mm',
            margins: [
                'top' => is_int($margins['top'] ?? null) ? $margins['top'] : 20,
                'right' => is_int($margins['right'] ?? null) ? $margins['right'] : 15,
                'bottom' => is_int($margins['bottom'] ?? null) ? $margins['bottom'] : 20,
                'left' => is_int($margins['left'] ?? null) ? $margins['left'] : 15,
            ],
            dpi: is_int($config['dpi'] ?? null) ? $config['dpi'] : 96,
        );
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
