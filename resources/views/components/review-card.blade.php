@props(['review'])

@php
    $movie = $review->movie;
    $user = $review->user;
    $poster = $movie?->posterSource();
    $initial = mb_strtoupper(mb_substr(trim((string) ($user?->name ?? $user?->username ?? '?')), 0, 1) ?: '?');
@endphp

<article class="rounded-xl border border-white/10 bg-[#2A0A2E]/70 p-4 backdrop-blur-sm">
    <div class="flex items-start gap-3">
        <div class="w-16 shrink-0">
            @if ($movie && $poster)
                <img src="{{ str_starts_with($poster, 'http') ? $poster : asset(ltrim($poster, '/')) }}" alt="{{ $movie->title }} poster" class="aspect-[2/3] w-full rounded-md border border-white/10 object-cover" loading="lazy" />
            @else
                <x-poster-art :title="$movie?->title ?? 'Review placeholder'" subtitle="Placeholder" />
            @endif
        </div>
        <div class="min-w-0">
            <h3 class="font-bebas text-2xl leading-none tracking-wide">{{ $movie?->title ?? 'Untitled review' }}</h3>
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
