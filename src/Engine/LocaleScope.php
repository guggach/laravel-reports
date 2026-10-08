<?php

declare(strict_types=1);

namespace Guggach\Reports\Engine;

use Illuminate\Support\Carbon;

/**
 * Sets the application and Carbon locale for the duration of a callback and
 * restores the previous state afterwards ("try/finally"). Scopes can be nested
 * arbitrarily, which is what makes per-detail (per-form-instance) languages
 * possible later without a rebuild: wrap each render in its own scope.
 */
final readonly class LocaleScope
{
    public function __construct(
        private ?string $locale = null,
        private ?string $fallback = null,
    ) {}

    public function locale(): ?string
    {
        return $this->locale;
    }

    public function fallback(): ?string
    {
        return $this->fallback;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public function run(callable $callback): mixed
    {
        $app = app();

        $previousLocale = $app->getLocale();
        $previousFallback = config('app.fallback_locale');
        $previousCarbon = Carbon::getLocale();

        $locale = $this->locale ?? $previousLocale;
        $fallback = $this->fallback ?? config('reports.fallback_locale');

        $app->setLocale($locale);

        if (is_string($fallback) && $fallback !== '') {
            $app->setFallbackLocale($fallback);
        }

        Carbon::setLocale($locale);

        try {
            return $callback();
        } finally {
            $app->setLocale($previousLocale);
            config(['app.fallback_locale' => $previousFallback]);
            Carbon::setLocale($previousCarbon);
        }
    }
}
