@props(['review'])

@php
    // The movie lives in TMDB, so the card uses the snapshot stored on the
    // review rather than a `$review->movie` relation.
    $poster = $review->posterSource();
    $user = $review->user;
    $title = trim((string) $review->movie_title) !== '' ? $review->movie_title : 'Untitled review';
    $initial = mb_strtoupper(mb_substr(trim((string) ($user?->name ?? $user?->username ?? '?')), 0, 1) ?: '?');
@endphp

<article class="rounded-xl border border-white/10 bg-[#2A0A2E]/70 p-4 backdrop-blur-sm">
    <div class="flex items-start gap-3">
        <div class="w-16 shrink-0">
            @if ($poster)
                <img src="{{ $poster }}" alt="{{ $title }} poster" class="aspect-[2/3] w-full rounded-md border border-white/10 object-cover" loading="lazy" />
            @else
                <x-poster-art :title="$title" subtitle="Placeholder" />
            @endif
        </div>
        <div class="min-w-0">
            <h3 class="font-bebas text-2xl leading-none tracking-wide">{{ $title }}</h3>
            <p class="mt-1 flex items-center gap-2 text-xs text-stone-300">
                <span aria-hidden="true" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 font-bebas text-sm">{{ $initial }}</span>
                <span class="truncate">{{ $user?->username ?? $user?->name ?? 'MovieREV member' }}</span>
            </p>
            <div class="mt-2">
                <x-rating-stars :value="$review->rating" />
            </div>
        </div>
    </div>
    <p class="mt-3 text-xs leading-relaxed text-stone-200">
        {{ $review->body ? \Illuminate\Support\Str::limit($review->body, 190) : 'No written review yet.' }}
    </p>
</article>
