<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Definitions
    |--------------------------------------------------------------------------
    |
    | Base path and PSR-4 namespace where report classes and their sources
    | live. Both are configurable so a host application can move them.
    |
    */

    'path' => resource_path('reports'),

    'namespace' => 'App\\Reports',

    'source_namespace' => 'App\\Reports\\Sources',

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
    | Page setup
    |--------------------------------------------------------------------------
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
    | "blade-livewire", "inertia-vue" or "inertia-react".
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
