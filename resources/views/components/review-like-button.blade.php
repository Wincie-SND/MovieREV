@props(['review'])

@php
    $likeCount = (int) ($review->likes_count ?? 0);
    $isLiked = (bool) ($review->liked_by_current_user ?? false);
    $isOwnReview = auth()->check() && (int) auth()->id() === (int) $review->user_id;
    $buttonClass = $isLiked
        ? 'border-red-500/40 bg-red-500/10 text-red-200 hover:bg-red-500/20'
        : 'border-white/10 bg-white/[0.03] text-stone-300 hover:border-red-500/40 hover:text-red-200';
@endphp

<div class="mt-4 flex items-center gap-3">
    @if ($isOwnReview)
        <span class="inline-flex items-center gap-2 text-xs text-stone-400" aria-label="{{ $likeCount }} {{ \Illuminate\Support\Str::plural('like', $likeCount) }}">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 text-red-300" fill="currentColor">
                <path d="M12 21s-7.2-4.35-9.6-9.15C.1 7.55 2.35 3 6.6 3c2.3 0 4.05 1.35 5.4 3.15C13.35 4.35 15.1 3 17.4 3c4.25 0 6.5 4.55 4.2 8.85C19.2 16.65 12 21 12 21Z" />
            </svg>
            {{ $likeCount }} {{ \Illuminate\Support\Str::plural('like', $likeCount) }}
        </span>
    @elseif (auth()->check())
        <form method="POST" action="{{ $isLiked ? route('reviews.unlike', $review) : route('reviews.like', $review) }}" class="m-0">
            @csrf
            @if ($isLiked)
                @method('DELETE')
            @endif
            <button type="submit" aria-label="{{ $isLiked ? 'Unlike this review' : 'Like this review' }}" aria-pressed="{{ $isLiked ? 'true' : 'false' }}" class="inline-flex cursor-pointer items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors {{ $buttonClass }}">
                <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7.2-4.35-9.6-9.15C.1 7.55 2.35 3 6.6 3c2.3 0 4.05 1.35 5.4 3.15C13.35 4.35 15.1 3 17.4 3c4.25 0 6.5 4.55 4.2 8.85C19.2 16.65 12 21 12 21Z" />
                </svg>
                {{ $isLiked ? 'Liked' : 'Like' }}
                <span class="text-stone-400">{{ $likeCount }}</span>
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="no-underline inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-3 py-1.5 text-xs font-semibold text-stone-300 transition-colors hover:border-red-500/40 hover:text-red-200">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7.2-4.35-9.6-9.15C.1 7.55 2.35 3 6.6 3c2.3 0 4.05 1.35 5.4 3.15C13.35 4.35 15.1 3 17.4 3c4.25 0 6.5 4.55 4.2 8.85C19.2 16.65 12 21 12 21Z" />
            </svg>
            Log in to like <span class="text-stone-400">{{ $likeCount }}</span>
        </a>
    @endif
</div>
