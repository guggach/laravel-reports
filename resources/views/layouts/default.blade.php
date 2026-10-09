<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Report' }}</title>
    <style>
        /*
         * Default report layout. Replaced by the layout/theming concept in
         * docs/spec.md (section 5.5). Page setup follows config('reports.paper').
         */
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 15mm;
        }

        body {
            font-family: sans-serif;
            color: #1f2937;
            margin: 0;
        }
    </style>
</head>
<body data-page-size="{{ $pageSetup->size ?? 'a4' }}" data-page-orientation="{{ $pageSetup->orientation ?? 'portrait' }}">
    <main>
        {!! $content ?? '' !!}
    </main>
</body>
</html>
