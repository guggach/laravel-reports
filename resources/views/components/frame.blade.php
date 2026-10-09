@props([
    'url',
    'pageSetup',
    'title' => null,
])

@php
    $landscape = $pageSetup->orientation === 'landscape';
    $size = strtolower($pageSetup->size);

    [$width, $height] = match ($size) {
        'a4' => $landscape ? [297, 210] : [210, 297],
        'a3' => $landscape ? [420, 297] : [297, 420],
        'a5' => $landscape ? [210, 148] : [148, 210],
        default => $landscape ? [297, 210] : [210, 297],
    };

    $margins = $pageSetup->margins;
    $contentHeight = $height - $margins['top'] - $margins['bottom'];
@endphp

<div class="report-frame"
     data-page-size="{{ $pageSetup->size }}"
     data-page-orientation="{{ $pageSetup->orientation }}">
    <div class="report-frame__chrome">
        <span class="report-frame__title">{{ $title }}</span>
        <span class="report-frame__actions">
            <button type="button"
                    onclick="var f=this.closest('.report-frame').querySelector('.report-frame__iframe'); f.contentWindow.focus(); f.contentWindow.print();">
                Drucken
            </button>
        </span>
    </div>

    <div class="report-frame__viewport">
        <div class="report-frame__sheet"
             style="width: {{ $width }}mm; min-height: {{ $height }}mm; padding: {{ $margins['top'] }}mm {{ $margins['right'] }}mm {{ $margins['bottom'] }}mm {{ $margins['left'] }}mm;">
            <iframe class="report-frame__iframe"
                    src="{{ $url }}"
                    title="{{ $title }}"
                    style="min-height: {{ $contentHeight }}mm;"
                    onload="this.style.height='0px'; this.style.height=this.contentWindow.document.documentElement.scrollHeight+'px';"></iframe>
        </div>
    </div>
</div>

<style>
    .report-frame__chrome { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 16px; background: #111827; color: #f9fafb; }
    .report-frame__title { font-weight: 600; }
    .report-frame__viewport { background: #e5e7eb; padding: 28px; display: flex; justify-content: center; }
    .report-frame__sheet { background: #fff; box-sizing: border-box; box-shadow: 0 2px 12px rgba(0, 0, 0, .28); }
    .report-frame__iframe { display: block; width: 100%; border: 0; }

    @media print {
        .report-frame__chrome, .report-frame__viewport { display: none; }
        .report-frame__sheet { width: auto; min-height: 0; padding: 0; box-shadow: none; }
        .report-frame__iframe { min-height: 0; }
    }
</style>
