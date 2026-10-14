@props(['movie'])

@php
    $poster = $movie->posterSource();
    $average = (float) ($movie->average_rating ?? 0);
    $reviewCount = (int) ($movie->reviews_count ?? 0);
@endphp

<article id="movie-{{ $movie->id }}" class="overflow-hidden rounded-xl border border-white/10 bg-[#150017]/85 backdrop-blur-sm">
    @if ($poster)
        <img src="{{ str_starts_with($poster, 'http') ? $poster : asset(ltrim($poster, '/')) }}" alt="{{ $movie->title }} poster" class="aspect-[2/3] w-full object-cover" loading="lazy" />
    @else
        <x-poster-art :title="$movie->title" subtitle="Placeholder" />
    @endif
    <div class="p-3">
        <h3 class="font-bebas text-xl leading-none tracking-wide">{{ $movie->title }}</h3>
        <p class="mt-1 text-[11px] uppercase tracking-[0.18em] text-stone-400">
            {{ $movie->release_year ?? 'Year TBD' }}
            @if ($movie->genre)
                • {{ $movie->genre }}
            @endif
        </p>
        <div class="mt-2 flex items-center justify-between gap-2">
            <x-rating-stars :value="$average" />
            <span class="text-[11px] text-stone-400">{{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
        </div>
    </div>
</article>
