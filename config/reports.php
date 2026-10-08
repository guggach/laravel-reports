<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Definitions
    |--------------------------------------------------------------------------
    |
    | Reports live in self-contained module folders below "path". The path and
    | the PSR-4 "namespace" must form a consistent pair (the path is the PSR-4
    | target directory of the namespace). The default app/Reports + App\Reports
    | needs no composer change. Any other path must be registered by the host
    | application in composer.json (autoload.psr-4).
    |
    */

    'path' => app_path('Reports'),

    'namespace' => 'App\\Reports',

    /*
    |--------------------------------------------------------------------------
    | Shared layout (corporate identity)
    |--------------------------------------------------------------------------
    |
    | The layout is the single deliberate exception to the self-contained
    | report module: most reports share one corporate identity, so it lives in
    | a shared directory that reports reference instead of copying.
    |
    */

    'layout_path' => app_path('Reports/Layout'),

    'layout_namespace' => 'App\\Reports\\Layout',

    /*
    |--------------------------------------------------------------------------
    | Rendering
    |--------------------------------------------------------------------------
    |
    | One Chromium renderer with two modes: "flow" (Chromium paginates) and
    | "strict" (fixed band heights, arithmetic pagination).
    |
    | These values are plain on purpose. When the config is published into the
    | host application, environment overrides can be added there.
    |
    */

    'driver' => 'browsershot',

    'default_mode' => 'flow',

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | "locale" defaults to the application locale (null). "fallback_locale" is
    | used when a translation is missing. "locales" is an allow-list for
    | validation and the generated filter UI. The host application owns the
    | language handling; a locale may later be resolved per detail/form record.
    |
    */

    'locale' => null,

    'fallback_locale' => 'en',

    'locales' => ['en', 'de'],

    /*
    |--------------------------------------------------------------------------
    | Page setup
    |--------------------------------------------------------------------------
    |
    | Global defaults; a layout or a report may override individual values.
    |
    */

    'paper' => [
        'size' => 'a4',
        'orientation' => 'portrait',
        'unit' => 'mm',
        'margins' => [
            'top' => 20,
            'right' => 15,
            'bottom' => 20,
            'left' => 15,
        ],
        'dpi' => 96,
    ],

    /*
    |--------------------------------------------------------------------------
    | Output / archive
    |--------------------------------------------------------------------------
    */

    'disk' => 'local',

    'archive_path' => 'reports/archive',

    /*
    |--------------------------------------------------------------------------
    | Presets
    |--------------------------------------------------------------------------
    |
    | The user model stored on presets and outputs. Set it to your application
    | user class, e.g. \App\Models\User::class.
    |
    */

    'user_model' => null,

    'permissions' => [
        'manage_global_presets' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI stubs
    |--------------------------------------------------------------------------
    |
    | The optional filter form and preview frame are stack-specific:
    | "blade-livewire", "inertia-vue" or "inertia-react". The filter UI must
    | pass the active locale through so generated labels are translated.
    |
    */

    'stubs' => [
        'frontend' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    'pdf' => [
        'options' => [
            'print_background' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exports (planned)
    |--------------------------------------------------------------------------
    */

    'exports' => [
        'word' => null,
        'excel' => null,
    ],

];
