@props(['review', 'excerpt' => true, 'showMovie' => true])

@php
    // The movie lives in TMDB, so the card uses the snapshot stored on the
    // review rather than a `$review->movie` relation. Every value below is
    // rendered through Blade's escaping.
    $poster = $review->posterSource();
    $user = $review->user;
    $movieTitle = trim((string) $review->movie_title) !== '' ? $review->movie_title : 'Untitled review';
    $reviewTitle = trim((string) $review->title);
    $displayName = $user?->username ?? $user?->name ?? 'MovieREV member';
    $initial = mb_strtoupper(mb_substr(trim((string) $displayName), 0, 1) ?: '?');
    $posted = $review->created_at;
    $body = trim((string) $review->body);
    $isOwner = auth()->check() && auth()->id() === $review->user_id;
@endphp

<article class="rounded-xl border border-white/10 bg-[#2A0A2E]/70 p-4 backdrop-blur-sm">
    <div class="flex items-start gap-3">
        @if ($showMovie)
            <div class="w-16 shrink-0">
                @if ($poster)
                    <img src="{{ $poster }}" alt="{{ $movieTitle }} poster" class="aspect-[2/3] w-full rounded-md border border-white/10 object-cover" loading="lazy" />
                @else
                    <x-poster-art :title="$movieTitle" subtitle="Placeholder" />
                @endif
            </div>
        @endif
        <div class="min-w-0 flex-1">
            @if ($showMovie)
                <h3 class="font-bebas text-2xl leading-none tracking-wide">{{ $movieTitle }}</h3>
            @elseif ($reviewTitle !== '')
                <h3 class="font-bebas text-2xl leading-none tracking-wide">{{ $reviewTitle }}</h3>
            @endif
            <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-stone-300">
                @if ($user)
                    <a href="{{ route('users.show', $user->username) }}" class="no-underline inline-flex items-center gap-2 text-stone-300 transition-colors hover:text-red-300">
                        <span aria-hidden="true" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 font-bebas text-sm">{{ $initial }}</span>
                        <span class="truncate">{{ $displayName }}</span>
                    </a>
                @else
                    <span aria-hidden="true" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/10 font-bebas text-sm">{{ $initial }}</span>
                    <span class="truncate">{{ $displayName }}</span>
                @endif
                @if ($posted)
                    <span aria-hidden="true" class="text-stone-600">•</span>
                    <time datetime="{{ $posted->toDateString() }}" class="text-stone-400">{{ $posted->format('M j, Y') }}</time>
                @endif
            </p>
            <div class="mt-2">
                <x-rating-stars :value="$review->rating" />
            </div>
        </div>
    </div>

    @if ($showMovie && $reviewTitle !== '')
        <p class="mt-3 text-sm font-bold text-white">{{ $reviewTitle }}</p>
    @endif

    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-stone-200">
        @if ($body !== '')
            {{ $excerpt ? \Illuminate\Support\Str::limit($body, 190) : $body }}
        @else
            No written review yet.
        @endif
    </p>

    <x-review-like-button :review="$review" />

    @if ($isOwner)
        <div class="mt-4 flex flex-wrap items-center gap-4 border-t border-white/10 pt-3">
            <a href="{{ route('movies.show', $review->tmdb_id) }}?edit={{ $review->id }}#write-review"
               class="no-underline text-xs font-bold uppercase tracking-[0.14em] text-stone-300 transition-colors hover:text-red-400">Edit</a>
            <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="m-0"
                  onsubmit="return confirm('Delete your review? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="cursor-pointer border-0 bg-transparent p-0 text-xs font-bold uppercase tracking-[0.14em] text-stone-400 transition-colors hover:text-red-400">Delete</button>
            </form>
            @if ($review->updated_at && $review->updated_at->gt($review->created_at))
                <span class="text-[11px] uppercase tracking-[0.14em] text-stone-500">Edited</span>
            @endif
        </div>
    @endif
</article>
