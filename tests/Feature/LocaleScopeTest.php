<?php

declare(strict_types=1);

use Guggach\Reports\Engine\LocaleScope;
use Illuminate\Support\Carbon;

it('sets and restores the application locale', function (): void {
    app()->setLocale('en');

    $result = (new LocaleScope('de'))->run(fn () => app()->getLocale());

    expect($result)->toBe('de');
    expect(app()->getLocale())->toBe('en');
});

it('restores the application locale when the callback throws', function (): void {
    app()->setLocale('en');

    try {
        (new LocaleScope('de'))->run(function (): void {
            throw new RuntimeException('boom');
        });
    } catch (RuntimeException) {
        // expected
    }

    expect(app()->getLocale())->toBe('en');
});

it('nests locales and restores the previous scope', function (): void {
    app()->setLocale('en');

    $seen = [];

    (new LocaleScope('de'))->run(function () use (&$seen): void {
        $seen[] = app()->getLocale();

        (new LocaleScope('fr'))->run(function () use (&$seen): void {
            $seen[] = app()->getLocale();
        });

        $seen[] = app()->getLocale();
    });

    expect($seen)->toBe(['de', 'fr', 'de']);
    expect(app()->getLocale())->toBe('en');
});

it('sets the carbon locale within the scope', function (): void {
    Carbon::setLocale('en');

    (new LocaleScope('de'))->run(function (): void {
        expect(Carbon::getLocale())->toBe('de');
    });

    expect(Carbon::getLocale())->toBe('en');
});

it('uses the configured fallback locale within the scope', function (): void {
    config([
        'app.fallback_locale' => 'en',
        'reports.fallback_locale' => 'de',
    ]);

    (new LocaleScope('fr'))->run(function (): void {
        expect(app()->getLocale())->toBe('fr');
        expect(config('app.fallback_locale'))->toBe('de');
    });

    expect(config('app.fallback_locale'))->toBe('en');
});
