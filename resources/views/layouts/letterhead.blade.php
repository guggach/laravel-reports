<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="page-size" content="{{ $pageSetup->size }}-{{ $pageSetup->orientation }}">
    <title>{{ $title ?? 'Report' }}</title>
    <style>
        @page { size: {{ strtoupper($pageSetup->size) }} {{ $pageSetup->orientation }}; }
        body { font-family: sans-serif; margin: 0; }
        .letterhead-header { border-bottom: 2px solid #1f2937; padding: 8px 0; }
        .letterhead-footer { border-top: 1px solid #ccc; margin-top: 24px; padding-top: 8px; color: #666; }
    </style>
</head>
<body data-layout="letterhead" data-page-size="{{ $pageSetup->size ?? 'a4' }}" data-page-orientation="{{ $pageSetup->orientation ?? 'portrait' }}">
    <header class="letterhead-header">
        {{ $meta['company'] ?? 'Company' }} {{-- logo slot --}}
    </header>

    <main>
        {!! $content ?? '' !!}
    </main>

    <footer class="letterhead-footer">
        {{ $meta['footer'] ?? 'Generated report' }}
    </footer>
</body>
</html>
