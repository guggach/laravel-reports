<?php

declare(strict_types=1);

namespace Guggach\Reports\Layouts;

use Guggach\Reports\Definition\PageSetup;

/**
 * A layout is the reusable outer chrome of a report (logo, letterhead,
 * header/footer, typography, colors). It is the single deliberate exception
 * to the self-contained report rule (see docs/spec.md 5.3/5.5).
 */
abstract class Layout
{
    /**
     * The Blade view that provides the outer HTML shell. It receives the
     * rendered report as `$content` plus the named slots below.
     */
    abstract public function view(): string;

    public function key(): string
    {
        return static::class;
    }

    /**
     * An optional parent layout view (like Blade @extends).
     */
    public function baseLayout(): ?string
    {
        return null;
    }

    /**
     * Layout-level PageSetup defaults. The report may override them; the
     * package config provides the global fallback (see docs/spec.md 5.5).
     */
    public function pageSetup(): ?PageSetup
    {
        return null;
    }
}
