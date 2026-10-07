<?php

declare(strict_types=1);

namespace Guggach\Reports\Sources;

final readonly class ReportParameter
{
    public function __construct(
        public string $name,
        public string $type = 'string',
        public bool $required = false,
        public mixed $default = null,
    ) {}
}
