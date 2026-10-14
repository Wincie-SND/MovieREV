@props(['title' => 'Poster placeholder', 'subtitle' => null])

@php
    $label = trim((string) $title) !== '' ? trim((string) $title) : 'Poster placeholder';
    $hue = crc32(mb_strtolower($label)) % 360;
@endphp

<div
    {{ $attributes->class('relative flex aspect-[2/3] w-full flex-col items-center justify-center gap-2 overflow-hidden rounded-lg border border-white/10 p-3 text-center') }}
    role="img"
    aria-label="{{ $label }} poster placeholder"
    style="background: linear-gradient(160deg, hsl({{ $hue }} 42% 18%), hsl({{ ($hue + 34) % 360 }} 64% 8%) 72%, #050308);"
>
    <svg class="h-8 w-8 text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 9h16M4 9l1.5-3.5h13L20 9M6 9v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9M9.5 13.5l2 2 3.5-4" />
    </svg>
    <p class="font-bebas text-lg leading-none tracking-[0.18em] text-white/80">Poster</p>
    <p class="text-[11px] font-semibold uppercase leading-snug tracking-[0.12em] text-white/55">{{ \Illuminate\Support\Str::limit($label, 42) }}</p>
    @if ($subtitle)
        <p class="text-[10px] leading-snug text-white/40">{{ $subtitle }}</p>
    @endif
</div>
