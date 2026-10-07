<?php

declare(strict_types=1);

namespace Guggach\Reports\Layouts;

abstract class Layout
{
    abstract public function view(): string;

    public function key(): string
    {
        return static::class;
    }

    public function baseLayout(): ?string
    {
        return null;
    }
}
