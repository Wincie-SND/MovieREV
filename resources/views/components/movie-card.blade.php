@props(['movie'])

@php
    $poster = $movie->posterSource();
    $average = (float) ($movie->average_rating ?? 0);
    $ratingCount = (int) ($movie->reviews_count ?? 0);
    // Only render the parts of the metadata line that TMDB actually published,
    // instead of filling the gaps with "Year TBD".
    $details = collect([$movie->release_year, $movie->genre])->filter()->implode(' • ');
@endphp

<a id="movie-{{ $movie->id }}" href="{{ route('movies.show', $movie->id) }}"
   class="no-underline group block overflow-hidden rounded-xl border border-white/10 bg-[#150017]/85 backdrop-blur-sm transition-colors hover:border-red-500/50 hover:no-underline">
    @if ($poster)
        <img src="{{ $poster }}" alt="{{ $movie->title }} poster" class="aspect-[2/3] w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]" loading="lazy" />
    @else
        <x-poster-art :title="$movie->title" subtitle="No poster on TMDB" />
    @endif
    <div class="p-3">
        <h3 class="font-bebas text-xl leading-none tracking-wide transition-colors group-hover:text-red-400">{{ $movie->title }}</h3>
        @if ($details !== '')
            <p class="mt-1 text-[11px] uppercase tracking-[0.18em] text-stone-400">{{ $details }}</p>
        @endif
        <div class="mt-2 flex items-center justify-between gap-2">
            <x-rating-stars :value="$average" />
            <span class="text-[11px] text-stone-400">{{ $ratingCount }} {{ \Illuminate\Support\Str::plural('rating', $ratingCount) }}</span>
        </div>
    </div>
</a>
