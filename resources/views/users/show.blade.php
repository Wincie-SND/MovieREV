@extends('layouts.app')

@section('title', $profile->username.' — MovieREV')

@section('content')
    @include('partials.backdrop')

<div id="top" class="relative min-h-screen text-white [background:linear-gradient(180deg,#0a040f_0%,#13051b_46%,#07040c_100%)]">
    @include('partials.header', ['search' => ''])

    <main class="mx-auto w-full max-w-[1800px] px-4 pb-16 sm:px-6 lg:px-8 2xl:px-10">
        @if (session('status'))
            <p role="status" class="mt-6 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-200">{{ session('status') }}</p>
        @endif

        <section class="relative mt-6 overflow-hidden rounded-2xl border border-white/10 bg-[#10020d]/85 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)] backdrop-blur-sm">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(147,51,234,0.24),transparent_48%),linear-gradient(120deg,rgba(49,16,72,0.75),rgba(8,3,13,0.95))]" aria-hidden="true"></div>
            <div class="relative flex flex-col gap-6 p-6 sm:flex-row sm:items-center sm:p-9">
                <div aria-hidden="true" class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full border-4 border-white/10 bg-gradient-to-br from-fuchsia-700 to-indigo-950 font-bebas text-5xl shadow-xl sm:h-36 sm:w-36">
                    {{ mb_strtoupper(mb_substr(trim($profile->username), 0, 1)) }}
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="m-0 text-3xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif] sm:text-4xl">{{ $profile->name }}</h1>
                    <p class="mt-1 text-sm font-semibold tracking-wide text-fuchsia-200">{{ '@'.$profile->username }}</p>
                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-stone-300">Movie fan sharing reviews and discoveries on MovieREV.</p>
                    @if ($profile->created_at)
                        <p class="mt-3 text-xs uppercase tracking-[0.14em] text-stone-500">Member since {{ $profile->created_at->format('M Y') }}</p>
                    @endif
                </div>

                @if (auth()->check() && ! $isOwnProfile)
                    @if ($isFollowing)
                        <form method="POST" action="{{ route('users.unfollow', $profile->username) }}" class="m-0 shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full rounded-full border border-white/20 px-6 py-3 text-xs font-bold uppercase tracking-[0.16em] text-white transition-colors hover:border-red-400 hover:bg-red-500/10 sm:w-auto">Following · Unfollow</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('users.follow', $profile->username) }}" class="m-0 shrink-0">
                            @csrf
                            <button type="submit" class="w-full rounded-full bg-red-500 px-7 py-3 text-xs font-bold uppercase tracking-[0.16em] text-white transition-colors hover:bg-red-600 sm:w-auto">Follow</button>
                        </form>
                    @endif
                @elseif (! auth()->check())
                    <a href="{{ route('login') }}" class="no-underline shrink-0 rounded-full bg-red-500 px-7 py-3 text-center text-xs font-bold uppercase tracking-[0.16em] text-white transition-colors hover:bg-red-600">Sign in to follow</a>
                @endif
            </div>

            <div class="relative grid grid-cols-2 border-t border-white/10 sm:grid-cols-4">
                @foreach ([['Films', $favoriteFilmCount], ['Reviews', $reviewCount], ['Following', $followingCount], ['Followers', $followerCount]] as [$label, $count])
                    <div class="border-b border-white/10 px-4 py-5 text-center last:border-b-0 sm:border-b-0 sm:border-r sm:px-6 sm:last:border-r-0">
                        <p class="m-0 text-2xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">{{ number_format($count) }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mt-10" aria-labelledby="favorite-films-heading">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 id="favorite-films-heading" class="m-0 text-2xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif] sm:text-3xl">Favorite Films</h2>
                    <p class="mt-2 text-sm text-stone-400">Films in {{ '@'.$profile->username }}’s Favorites list</p>
                </div>
                @if ($isOwnProfile)
                    <a href="{{ route('movie-lists.index') }}" class="no-underline rounded-full border border-white/20 px-4 py-2 text-[10px] font-bold uppercase tracking-[0.14em] text-white transition-colors hover:border-red-500/50 hover:text-red-300">Manage my lists</a>
                @endif
            </div>

            @if ($favoriteFilmCount > 0)
                <div class="mt-5 rounded-xl border border-white/10 bg-white/[0.04] px-5 py-6 text-sm text-stone-300">
                    {{ number_format($favoriteFilmCount) }} {{ \Illuminate\Support\Str::plural('film', $favoriteFilmCount) }} saved to this member’s Favorites list.
                </div>
            @else
                <div class="mt-5 rounded-xl border border-dashed border-white/15 bg-white/[0.03] px-5 py-8 text-center">
                    <p class="m-0 font-bebas text-xl tracking-wide text-stone-300">No favorite films yet</p>
                    <p class="mt-1 text-sm text-stone-500">Saved films will appear here.</p>
                </div>
            @endif
        </section>

        <section class="mt-10" aria-labelledby="recent-reviews-heading">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <h2 id="recent-reviews-heading" class="m-0 text-2xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif] sm:text-3xl">Recently Reviewed</h2>
                    <p class="mt-2 text-sm text-stone-400">Latest reviews from {{ '@'.$profile->username }}</p>
                </div>
                <span class="text-xs font-bold uppercase tracking-[0.16em] text-stone-500">{{ number_format($reviewCount) }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
            </div>

            @if ($recentReviews->isNotEmpty())
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($recentReviews as $review)
                        @php($poster = $review->posterSource())
                        <a href="{{ route('movies.show', $review->tmdb_id) }}" class="no-underline group overflow-hidden rounded-xl border border-white/10 bg-[#2A0A2E]/60 transition-colors hover:border-red-500/40">
                            @if ($poster)
                                <img src="{{ $poster }}" alt="{{ $review->movie_title }} poster" class="aspect-[16/9] w-full object-cover" loading="lazy" />
                            @else
                                <div class="flex aspect-[16/9] items-center justify-center bg-gradient-to-br from-fuchsia-950 to-zinc-950 px-5 text-center font-bebas text-2xl text-white/70">{{ $review->movie_title }}</div>
                            @endif
                            <div class="p-4">
                                <h3 class="m-0 truncate font-bebas text-2xl tracking-wide text-white group-hover:text-red-300">{{ $review->movie_title }}</h3>
                                <div class="mt-2"><x-rating-stars :value="$review->rating" /></div>
                                @if ($review->title)
                                    <p class="mt-2 truncate text-sm font-semibold text-stone-200">{{ $review->title }}</p>
                                @endif
                                <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-stone-400">{{ $review->body ?: 'No written review yet.' }}</p>
                                <time datetime="{{ $review->created_at->toDateString() }}" class="mt-3 block text-[10px] uppercase tracking-[0.14em] text-stone-500">{{ $review->created_at->format('M j, Y') }}</time>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="mt-5 rounded-xl border border-dashed border-white/15 bg-white/[0.03] px-5 py-8 text-center">
                    <p class="m-0 font-bebas text-xl tracking-wide text-stone-300">No reviews yet</p>
                    <p class="mt-1 text-sm text-stone-500">Reviews written by this member will appear here.</p>
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
