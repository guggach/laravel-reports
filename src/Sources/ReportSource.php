<?php

declare(strict_types=1);

namespace Guggach\Reports\Sources;

/**
 * Base class for report data sources. Concrete sources are report-local and
 * never shared between reports (see docs/spec.md 7.1).
 *
 * The host application brings the databases/models; adapters decide about
 * query push-down. The engine only consumes the normalized iterable returned
 * by records().
 */
abstract class ReportSource
{
    protected ?string $locale = null;

    /**
     * @return iterable<array-key, mixed>
     */
    abstract public function records(): iterable;

    /**
     * @return list<ReportField>
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * @return list<ReportParameter>
     */
    public function parameters(): array
    {
        return [];
    }

    public function key(): string
    {
        return static::class;
    }

    /**
     * The active locale of the current run. Host sources may use it to filter
     * language columns; translatable models just read the ambient app locale.
     */
    public function locale(): ?string
    {
        return $this->locale;
    }

    public function withLocale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
