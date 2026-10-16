@extends('layouts.app')

@section('title', 'Dashboard — MovieREV')

@section('content')
    @include('partials.backdrop')

<div id="top" class="relative min-h-screen text-white [background:linear-gradient(180deg,#0a040f_0%,#13051b_46%,#07040c_100%)]">
    @include('partials.header', ['search' => $search])

    <main class="relative mx-auto w-full max-w-[1800px] px-4 pb-16 sm:px-6 lg:px-8 2xl:px-10">
        @if ($search !== '')
            <p class="mt-6 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-stone-200 backdrop-blur-sm">
                Showing catalog matches for <strong class="text-white">“{{ $search }}”</strong>.
                @if (! $featuredMovie && $popularMovies->isEmpty() && $recentReviews->isEmpty())
                    No matches were found, so placeholder slots are shown below.
                @endif
            </p>
        @endif

        <section id="featured" aria-label="Featured movie" class="mt-6 overflow-hidden rounded-2xl border border-white/10 bg-[#10020d]/85 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)] backdrop-blur-sm">
            @if ($featuredMovie)
                @php($featuredPoster = $featuredMovie->posterSource())
                <div class="grid md:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
                    <div class="relative min-h-[280px] overflow-hidden md:min-h-[360px]">
                        @if ($featuredPoster)
                            <img src="{{ $featuredPoster }}" alt="{{ $featuredMovie->title }} featured art" class="absolute inset-0 h-full w-full object-cover" />
                        @else
                            <div class="absolute inset-0 [background:radial-gradient(circle_at_30%_20%,rgba(239,68,68,0.28),transparent_45%),linear-gradient(135deg,#2a0a2e,#0b020d_70%)]" aria-hidden="true"></div>
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 p-6 text-center">
                                <p class="font-bebas text-5xl tracking-[0.12em] text-white/25">{{ \Illuminate\Support\Str::limit(mb_strtoupper($featuredMovie->title), 22) }}</p>
                                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/45">Featured art placeholder</p>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-[#10020d] via-transparent to-transparent md:bg-gradient-to-r" aria-hidden="true"></div>
                    </div>
                    <div class="flex flex-col justify-center p-6 sm:p-10">
                        <p class="font-bebas text-sm tracking-[0.28em] text-red-500">Featured</p>
                        <h1 class="mt-1 font-bebas text-6xl leading-[0.95] tracking-wide sm:text-7xl">{{ $featuredMovie->title }}</h1>
                        <p class="mt-2 text-xs uppercase tracking-[0.18em] text-stone-300">
                            {{ $featuredMovie->release_year ?? 'Year TBD' }}
                            @if ($featuredMovie->genre)
                                • {{ $featuredMovie->genre }}
                            @endif
                            • ★ {{ number_format((float) ($featuredMovie->average_rating ?? 0), 1) }} TMDB rating
                        </p>
                        <p class="mt-4 max-w-xl text-sm leading-relaxed text-stone-200">
                            {{ $featuredMovie->description ? \Illuminate\Support\Str::limit($featuredMovie->description, 240) : 'TMDB has not published an overview for this title yet.' }}
                        </p>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ route('movies.show', $featuredMovie->id) }}" class="no-underline rounded-full bg-red-500 px-6 py-2 text-sm font-bold text-white hover:bg-red-600 hover:no-underline">View Film</a>
                            <a href="#films" class="no-underline rounded-full border border-white/20 px-6 py-2 text-sm font-bold text-white hover:bg-white/10 hover:no-underline">Browse Films</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="relative overflow-hidden p-8 sm:p-12">
                    <div class="absolute inset-0 [background:radial-gradient(circle_at_30%_20%,rgba(239,68,68,0.22),transparent_45%),linear-gradient(135deg,#2a0a2e,#0b020d_70%)]" aria-hidden="true"></div>
                    <div class="relative">
                        <p class="font-bebas text-sm tracking-[0.28em] text-red-500">Featured</p>
                        <h1 class="mt-1 font-bebas text-6xl leading-[0.95] sm:text-7xl">Featured title placeholder</h1>
                        <p class="mt-3 max-w-xl text-sm leading-relaxed text-stone-200">The catalog is loaded from TMDB, which has nothing to show yet. Check the API credential in <code>.env</code>, or try again once TMDB is reachable.</p>
                    </div>
                </div>
            @endif
        </section>

        <section id="recently-released" aria-labelledby="recently-released-heading" class="mt-12">
            <div class="flex items-end justify-between gap-4">
                <h2 id="recently-released-heading" class="font-bebas text-4xl tracking-wide">Recently Released</h2>
                <p class="hidden text-xs uppercase tracking-[0.18em] text-stone-400 sm:block">{{ $recentlyReleasedMovies->count() }} titles</p>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @forelse ($recentlyReleasedMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @empty
                    @for ($placeholder = 0; $placeholder < 5; $placeholder++)
                        <div>
                            <x-poster-art title="Placeholder title" subtitle="Awaiting entry" />
                            <p class="mt-2 font-bebas text-lg leading-none tracking-wide text-stone-300">Title placeholder</p>
                            <p class="mt-1 text-[11px] uppercase tracking-[0.18em] text-stone-500">Year placeholder</p>
                            <div class="mt-1"><x-rating-stars :value="0" /></div>
                        </div>
                    @endfor
                @endforelse
            </div>
            @if ($recentlyReleasedMovies->isEmpty())
                <p class="mt-3 text-sm text-stone-400">Placeholder slots are shown until the TMDB catalog loads.</p>
            @endif
        </section>

        <section id="films" aria-labelledby="popular-films-heading" class="mt-12">
            <div class="flex items-end justify-between gap-4">
                <h2 id="popular-films-heading" class="font-bebas text-4xl tracking-wide">Popular Films</h2>
                <p class="hidden text-xs uppercase tracking-[0.18em] text-stone-400 sm:block">{{ $movieCount }} titles</p>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
                @forelse ($popularMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @empty
                    @for ($placeholder = 0; $placeholder < 4; $placeholder++)
                        <div>
                            <x-poster-art title="Placeholder title" subtitle="Awaiting entry" />
                            <p class="mt-2 font-bebas text-lg leading-none tracking-wide text-stone-300">Title placeholder</p>
                            <p class="mt-1 text-[11px] uppercase tracking-[0.18em] text-stone-500">Year placeholder</p>
                            <div class="mt-1"><x-rating-stars :value="0" /></div>
                        </div>
                    @endfor
                @endforelse
            </div>
            @if ($popularMovies->isEmpty())
                <p class="mt-3 text-sm text-stone-400">Placeholder slots are shown until the TMDB catalog loads.</p>
            @endif
        </section>

        <section id="reviews-week" aria-labelledby="popular-reviews-heading" class="mt-12">
            <div class="flex items-end justify-between gap-4">
                <h2 id="popular-reviews-heading" class="font-bebas text-4xl tracking-wide">Popular Reviews This Week</h2>
                <a href="#reviews-week" class="no-underline text-xs font-bold uppercase tracking-[0.18em] text-red-400 transition-colors hover:text-red-300">See More &gt;</a>
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @forelse ($recentReviews as $review)
                    <x-review-card :review="$review" />
                @empty
                    @for ($placeholder = 0; $placeholder < 2; $placeholder++)
                        <div class="rounded-xl border border-white/10 bg-[#2A0A2E]/60 p-4 backdrop-blur-sm">
                            <div class="flex items-start gap-3">
                                <div class="w-16 shrink-0"><x-poster-art title="Review placeholder" subtitle="Placeholder" /></div>
                                <div>
                                    <p class="font-bebas text-2xl leading-none tracking-wide text-stone-300">Review placeholder</p>
                                    <p class="mt-1 text-xs uppercase tracking-[0.18em] text-stone-500">Awaiting member review</p>
                                    <div class="mt-2"><x-rating-stars :value="0" /></div>
                                </div>
                            </div>
                            <p class="mt-3 text-xs leading-relaxed text-stone-400">Review cards will appear here after members review a film.</p>
                        </div>
                    @endfor
                @endforelse
            </div>
        </section>
    </main>
</div>
@endsection
