<?php

declare(strict_types=1);

namespace Guggach\Reports\Sources;

/**
 * In-memory source backed by an array. Useful for tests and for reports whose
 * records are computed by the host application.
 */
final class ArraySource extends ReportSource
{
    /**
     * @param  iterable<array-key, mixed>  $records
     * @param  list<ReportField>  $fields
     * @param  list<ReportParameter>  $parameters
     */
    public function __construct(
        private readonly iterable $records = [],
        private readonly array $fields = [],
        private readonly array $parameters = [],
    ) {}

    public function records(): iterable
    {
        return $this->records;
    }

    public function fields(): array
    {
        return $this->fields;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
