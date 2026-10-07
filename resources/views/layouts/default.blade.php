<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Report')</title>
    <style>
        /*
         * Work in progress: the default report layout.
         * Replaced by the layout/theming concept in docs/spec.md (section 5.5).
         */
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
        }

        body {
            font-family: sans-serif;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <main>
        @yield('content')
    </main>
</body>
</html>
