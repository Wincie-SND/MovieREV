@extends('layouts.app')

@section('title', $detail->movie->title.' — MovieREV')

@section('content')
    @include('partials.backdrop')

@php
    $movie = $detail->movie;
    $poster = $movie->posterSource();
    $backdrop = $detail->backdropSource();
    $reviewCount = $reviews->count();

    // Only the facts TMDB actually published, joined into the design's single
    // metadata line ("2024 • 2h 06m • PG-13 • Action, Comedy").
    $meta = collect([
        $movie->release_year,
        $detail->runtimeLabel(),
        $detail->rated,
        $detail->genres === [] ? null : implode(', ', $detail->genres),
    ])->filter();
@endphp

<div id="top" class="relative min-h-screen text-white [background:linear-gradient(180deg,#0a040f_0%,#13051b_46%,#07040c_100%)]">
    @include('partials.header', ['search' => ''])

    <main class="relative mx-auto w-full max-w-6xl px-4 pb-16">
        <a href="{{ route('home') }}#films" class="no-underline mt-6 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-stone-400 transition-colors hover:text-red-400">
            <span aria-hidden="true">&larr;</span> Back to films
        </a>

        {{-- Hero: backdrop band, poster pulled up over it, then the title block. --}}
        <section aria-labelledby="movie-title" class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-[#10020d]/85 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)] backdrop-blur-sm">
            <div class="relative h-40 overflow-hidden sm:h-64">
                @if ($backdrop)
                    <img src="{{ $backdrop }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-70" />
                @else
                    <div class="absolute inset-0 [background:radial-gradient(circle_at_25%_20%,rgba(239,68,68,0.32),transparent_45%),linear-gradient(135deg,#2a0a2e,#0b020d_70%)]" aria-hidden="true"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-[#10020d] via-[#10020d]/45 to-transparent" aria-hidden="true"></div>
            </div>

            <div class="px-5 pb-6 sm:px-10 sm:pb-10">
                <div class="grid grid-cols-[110px_minmax(0,1fr)] gap-4 sm:grid-cols-[190px_minmax(0,1fr)] sm:gap-8">
                    <div class="relative z-10 -mt-16 sm:-mt-28">
                        @if ($poster)
                            <img src="{{ $poster }}" alt="{{ $movie->title }} poster" class="aspect-[2/3] w-full rounded-lg border border-white/15 object-cover shadow-[0_30px_60px_-30px_rgba(0,0,0,0.95)]" />
                        @else
                            <x-poster-art :title="$movie->title" subtitle="No poster on TMDB" />
                        @endif
                    </div>

                    <div class="min-w-0 sm:pt-8">
                        <h1 id="movie-title" class="m-0 text-3xl font-bold leading-[1.1] text-white [font-family:'Playfair_Display',Georgia,serif] sm:text-5xl">{{ $movie->title }}</h1>

                        @if ($detail->tagline)
                            <p class="mt-2 text-sm italic text-stone-400">“{{ $detail->tagline }}”</p>
                        @endif

                        @if ($meta->isNotEmpty())
                            <p class="mt-3 text-xs uppercase tracking-[0.18em] text-stone-300">{{ $meta->implode(' • ') }}</p>
                        @endif

                        <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2">
                            {{-- TMDB's own score is movie information, kept but clearly labelled. --}}
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-stone-500">TMDB</span>
                                <x-rating-stars :value="$movie->average_rating" />
                                <span class="text-xs text-stone-400">{{ number_format((float) $movie->average_rating, 1) }}/5 &middot; {{ number_format($movie->reviews_count) }} {{ \Illuminate\Support\Str::plural('rating', $movie->reviews_count) }}</span>
                            </div>

                            {{-- MovieREV's community score comes from member reviews only. --}}
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-red-400">MovieREV</span>
                                @if ($communityRating !== null)
                                    <x-rating-stars :value="$communityRating" />
                                    <span class="text-xs text-stone-300">{{ number_format($communityRating, 1) }}/5 &middot; {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
                                @else
                                    <span class="text-xs text-stone-400">No member reviews yet</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="#write-review" class="no-underline rounded-full bg-red-500 px-6 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:bg-red-600 hover:no-underline">Write a review</a>
                            <button type="button" aria-disabled="true" title="Watch lists are not available yet"
                                    class="cursor-not-allowed rounded-full border border-white/30 px-6 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white/60">
                                + Add to watch list
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Section links, not tabs: every section is on this page already, so
             they jump rather than swap content in place. --}}
        <nav aria-label="Sections" class="mt-8 flex flex-wrap gap-x-6 gap-y-2 border-b border-white/10 text-sm">
            <a href="#overview" class="no-underline -mb-px border-b-2 border-red-500 pb-3 font-bold text-white hover:text-red-400">Overview</a>
            <a href="#cast" class="no-underline -mb-px border-b-2 border-transparent pb-3 text-stone-400 transition-colors hover:text-white">Cast &amp; Crew</a>
            <a href="#reviews" class="no-underline -mb-px border-b-2 border-transparent pb-3 text-stone-400 transition-colors hover:text-white">Reviews</a>
            <a href="#details" class="no-underline -mb-px border-b-2 border-transparent pb-3 text-stone-400 transition-colors hover:text-white">Details</a>
        </nav>

        <section id="overview" aria-labelledby="synopsis-heading" class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)]">
            <div>
                <h2 id="synopsis-heading" class="m-0 text-3xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">Synopsis</h2>
                <p class="mt-4 max-w-3xl text-base leading-relaxed text-stone-300">
                    {{ $movie->description ?? 'TMDB has not published an overview for this title yet.' }}
                </p>
            </div>

            <dl id="details" class="h-fit rounded-xl border border-white/10 bg-[#150017]/85 p-6 backdrop-blur-sm">
                <h2 class="m-0 text-xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">Details</h2>

                @php
                    $rows = collect([
                        'Director' => $detail->director,
                        'Release year' => $movie->release_year,
                        'Runtime' => $detail->runtimeLabel(),
                        'Rated' => $detail->rated,
                        'Genres' => $detail->genres === [] ? null : implode(', ', $detail->genres),
                    ])->filter();
                @endphp

                <div class="mt-3">
                    @forelse ($rows as $label => $value)
                        <div class="flex items-start justify-between gap-4 border-b border-white/5 py-2 last:border-b-0">
                            <dt class="text-sm text-stone-400">{{ $label }}</dt>
                            <dd class="m-0 text-right text-sm text-white">{{ $value }}</dd>
                        </div>
                    @empty
                        <p class="text-sm text-stone-400">TMDB has no details for this title yet.</p>
                    @endforelse
                </div>
            </dl>
        </section>

        @if ($detail->hasCast())
            <section id="cast" aria-labelledby="cast-heading" class="mt-12">
                <h2 id="cast-heading" class="m-0 text-3xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">Cast &amp; Crew</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($detail->cast as $person)
                        <article class="flex items-center gap-3 rounded-xl border border-white/10 bg-[#150017]/85 p-3 backdrop-blur-sm">
                            @if ($person['photo_url'])
                                <img src="{{ $person['photo_url'] }}" alt="" class="h-12 w-12 shrink-0 rounded-lg object-cover" loading="lazy" />
                            @else
                                <span aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-fuchsia-950 font-bebas text-lg">{{ mb_strtoupper(mb_substr($person['name'], 0, 1)) }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="m-0 truncate text-sm font-bold text-white">{{ $person['name'] }}</p>
                                @if ($person['character'])
                                    <p class="m-0 truncate text-xs text-stone-400">{{ $person['character'] }}</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="reviews" aria-labelledby="reviews-heading" class="mt-12">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="reviews-heading" class="m-0 text-3xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">MovieREV Community Reviews</h2>
                    <p class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-400">Written by MovieREV members — never pulled from TMDB</p>
                </div>
                <p class="text-xs uppercase tracking-[0.18em] text-stone-400">{{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</p>
            </div>

            {{-- The community average is calculated from member ratings only. --}}
            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl border border-white/10 bg-[#150017]/85 p-5 backdrop-blur-sm">
                @if ($communityRating !== null)
                    <x-rating-stars :value="$communityRating" />
                    <span class="text-2xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">{{ number_format($communityRating, 1) }}<span class="text-sm text-stone-400">/5</span></span>
                    <span class="text-sm text-stone-300">from {{ $reviewCount }} MovieREV {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span>
                @else
                    <p class="m-0 text-sm text-stone-300">No MovieREV community rating yet — be the first to review this movie!</p>
                @endif
            </div>

            {{-- Members get the form; guests get a prompt to sign in first. --}}
            <div id="write-review" class="mt-6 scroll-mt-24 rounded-xl border border-white/10 bg-[#2A0A2E]/60 p-5 backdrop-blur-sm sm:p-6">
                @auth
                    @if ($editingReview)
                        <h3 class="m-0 font-bebas text-2xl tracking-wide text-white">Edit your review</h3>
                        <p class="mt-1 text-sm text-stone-400">Update your rating or rewrite your review of {{ $movie->title }}.</p>
                        <div class="mt-4">
                            <x-review-form :action="route('reviews.update', $editingReview)" method="PUT" :review="$editingReview"
                                            submit-label="Save changes" :cancel-url="route('movies.show', $movie->id).'#reviews'" />
                        </div>
                    @elseif ($myReview)
                        <h3 class="m-0 font-bebas text-2xl tracking-wide text-white">You have reviewed this film</h3>
                        <p class="mt-1 text-sm text-stone-400">MovieREV keeps one review per member per movie — edit or delete yours below.</p>
                        <div class="mt-4 flex flex-wrap items-center gap-4">
                            <a href="{{ route('movies.show', $movie->id) }}?edit={{ $myReview->id }}#write-review"
                               class="no-underline rounded-full bg-red-500 px-6 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:bg-red-600 hover:no-underline">Edit your review</a>
                            <form method="POST" action="{{ route('reviews.destroy', $myReview) }}" class="m-0"
                                  onsubmit="return confirm('Delete your review? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cursor-pointer rounded-full border border-white/20 bg-transparent px-6 py-2 text-xs font-bold uppercase tracking-[0.14em] text-white transition-colors hover:border-red-500/60 hover:bg-white/10">Delete review</button>
                            </form>
                        </div>
                    @else
                        <h3 class="m-0 font-bebas text-2xl tracking-wide text-white">Write a review</h3>
                        <p class="mt-1 text-sm text-stone-400">Tell other members what you thought of {{ $movie->title }}.</p>
                        <div class="mt-4">
                            <x-review-form :action="route('reviews.store', $movie->id)" method="POST" submit-label="Submit review" />
                        </div>
                    @endif
                @else
                    <h3 class="m-0 font-bebas text-2xl tracking-wide text-white">Want to review this movie?</h3>
                    <p class="mt-1 text-sm text-stone-300">
                        <a href="{{ route('login') }}" class="no-underline font-bold text-red-400 transition-colors hover:text-red-300">Log in</a>
                        or
                        <a href="{{ route('register') }}" class="no-underline font-bold text-red-400 transition-colors hover:text-red-300">create an account</a>
                        to rate this film and write a review. MovieREV reviews come from members like you.
                    </p>
                @endauth
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @forelse ($reviews as $review)
                    <x-review-card :review="$review" :excerpt="false" :show-movie="false" />
                @empty
                    <div class="rounded-xl border border-white/10 bg-[#2A0A2E]/60 p-6 backdrop-blur-sm md:col-span-2">
                        <p class="m-0 font-bebas text-2xl tracking-wide text-stone-300">No reviews yet</p>
                        <p class="mt-1 text-sm text-stone-400">No reviews yet. Be the first to review {{ $movie->title }}!</p>
                    </div>
                @endforelse
            </div>
        </section>

        @if ($detail->similar->isNotEmpty())
            <section id="similar" aria-labelledby="similar-heading" class="mt-12">
                <h2 id="similar-heading" class="m-0 text-3xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">Similar Films</h2>
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($detail->similar as $similar)
                        <x-movie-card :movie="$similar" />
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>
@endsection
